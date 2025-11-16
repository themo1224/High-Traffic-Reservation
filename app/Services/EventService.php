<?php

namespace App\Services;

use App\Repositories\EventRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class EventService
{
    protected string $indexCachePrefix = 'events:index'; // page-specific keys
    protected string $lockPrefix = 'lock:events_index';
    protected EventRepositoryInterface $repo;

    public function __construct(EventRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Paginated index using Redis (soft-TTL + safe lock).
     *
     * @param int $per
     * @param int $page
     * @param int $ttl fresh TTL seconds (e.g. 15)
     * @param int $lockTtl lock TTL seconds (e.g. 10)
     * @return \Illuminate\Support\Collection
     */
    public function index(int $per, int $page, int $ttl = 15, int $lockTtl = 10)
    {
        $page = max(1, $page);
        $per = max(1, $per);

        $cacheKey = "{$this->indexCachePrefix}:p{$page}:n{$per}";
        $lockKey = "{$this->lockPrefix}:p{$page}:n{$per}";

        // try read payload (JSON) from Redis
        $raw = Redis::get($cacheKey);
        if ($raw !== null) {
            $payload = json_decode($raw, true);
            $now = time();

            // if still fresh, return direct
            if (($payload['fresh_until'] ?? 0) >= $now) {
                return collect($payload['value']);
            }
        }

        // try to acquire lock using SET NX EX with a token
        $token = Str::random(40);
        // Use Redis SET with NX + EX flags. Depending on client, signature may work as array options too.
        $acquired = Redis::set($lockKey, $token, 'NX', 'EX', $lockTtl);

        if ($acquired) {
            try {
                // we own lock -> rebuild from repo
                $data = $this->repo->getIndex($page, $per);

                // write soft-TTL payload: fresh_until + hard TTL
                $freshSeconds = max(1, $ttl);                 // fresh window
                $hardSeconds = max(300, $freshSeconds * 20);  // keep payload longer as hard TTL

                $payloadToStore = [
                    'value' => $data,
                    'fresh_until' => time() + $freshSeconds,
                ];

                // store JSON with hard TTL
                Redis::setex($cacheKey, $hardSeconds, json_encode($payloadToStore));

                return $data;
            } finally {
                // safe release: only delete if token matches (Lua)
                $this->releaseLockLua($lockKey, $token);
            }
        }

        // lock not acquired -> rebuild directly via repo and write payload
        $data = $this->repo->getIndex($page, $per);
        $freshSeconds = max(1, $ttl);
        $hardSeconds = max(300, $freshSeconds * 20);
        $payloadToStore = [
            'value' => $data,
            'fresh_until' => time() + $freshSeconds,
        ];
        Redis::setex($cacheKey, $hardSeconds, json_encode($payloadToStore));
        return $data;
    }

    /**
     * Release lock safely using Lua to avoid deleting another owner's lock.
     */
    protected function releaseLockLua(string $lockKey, string $token): void
    {
        $script = <<<'LUA'
if redis.call("get", KEYS[1]) == ARGV[1] then
  return redis.call("del", KEYS[1])
else
  return 0
end
LUA;
        // eval(script, numKeys, key, arg1)
        Redis::eval($script, 1, $lockKey, $token);
    }

    /**
     * Invalidate page caches (naive).
     */
    public function invalidateIndexCache(int $page = null, int $per = null): void
    {
        if ($page && $per) {
            $key = "{$this->indexCachePrefix}:p{$page}:n{$per}";
            Redis::del($key);
            return;
        }

        for ($p = 1; $p <= 5; $p++) {
            $key = "{$this->indexCachePrefix}:p{$p}:n{$per}";
            Redis::del($key);
        }
    }
}
