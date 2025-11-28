<?php

namespace App\Services;

use App\Repositories\EventRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class EventService
{
    protected string $indexCachePrefix = 'events:index'; // page-specific keys

    // simplified: we no longer use distributed locks/soft-TTL in this service

    protected EventRepositoryInterface $repo;
    /**
     * Optional cache instance (used for easier testing). If null the Cache facade is used.
     *
     * @var mixed|null
     */
    protected $cache = null;

    public function __construct(EventRepositoryInterface $repo, $cache = null)
    {
        $this->repo = $repo;
        $this->cache = $cache;
    }

    /**
     * Paginated index using Redis (soft-TTL + safe lock).
     *
     * @param  int  $ttl  fresh TTL seconds (e.g. 15)
     * @param  int  $lockTtl  lock TTL seconds (e.g. 10)
     * @return \Illuminate\Support\Collection
     */
    public function index(int $per, int $page, int $ttl = 15, int $lockTtl = 10)
    {
        $page = max(1, $page);
        $per = max(1, $per);

        $cacheKey = "{$this->indexCachePrefix}:p{$page}:n{$per}";
        // Simple caching: single TTL (no lock or soft/hard TTL complexity).
        // Keep the signature compatible so callers that pass $lockTtl don't break.
        $ttlSeconds = max(1, $ttl);

        // prefer injected cache (useful in tests), otherwise use the Cache facade
        $cache = $this->cache;
        if ($cache) {
            return $cache->remember($cacheKey, $ttlSeconds, function () use ($page, $per) {
                return $this->repo->getIndex($page, $per);
            });
        }

        return Cache::remember($cacheKey, $ttlSeconds, function () use ($page, $per) {
            return $this->repo->getIndex($page, $per);
        });
    }

    /**
     * Release lock safely using Lua to avoid deleting another owner's lock.
     */
        // previously used to safely release distributed locks — removed in simplified service

    /**
     * Invalidate page caches (naive).
     */
    public function invalidateIndexCache(?int $page = null, ?int $per = null): void
    {
        if ($page && $per) {
            $key = "{$this->indexCachePrefix}:p{$page}:n{$per}";
            if ($this->cache) {
                $this->cache->forget($key);
            } else {
                Cache::forget($key);
            }

            return;
        }

        // naive: delete any cached pages for this prefix (Redis KEYS); ok for small scale/local
        $pattern = "{$this->indexCachePrefix}:*";
        $keys = Redis::keys($pattern);
        foreach ($keys as $k) {
            Redis::del($k);
        }
    }
}
