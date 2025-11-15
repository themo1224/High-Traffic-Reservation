<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EventService
{
    protected string $runningCacheKey = 'events:running';
    protected string $indexCachePrefix = 'events:index';
    protected string $lockPrefix = 'lock:events_index';

    
    /**
     * Simple paginated index of events with cache+lock protection.
     *
     * @param int $page 1-based
     * @param int $per
     * @param int $ttl seconds for cache
     * @param int $lockTtl seconds for lock
     * @return \Illuminate\Support\Collection
     */

     public function index(int $per, int $page, int $ttl, int $lockTtl)
     {
        $page = max(1, $page);
        $per = max(1, $per);
        $cacheKey = "{$this->indexCachePrefix}:p{$page}:n{$per}";
        $lockName = "{$this->lockPrefix}:p{$page}:n{$per}";

        //return cached if exists
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        //try to get lock to refresh 
        $lock = Cache::lock($lockName, $lockTtl);
        if ($lock->get()) {
            try {
                $data = $this->queryIndex($page, $per);
                Cache::put($cacheKey, $data, $ttl);
                return $data;
            } finally {
                $lock->release();
            }
        }

        // lock held -> return stale if any
        $stale = Cache::get($cacheKey);
        if ($stale !== null) {
            return $stale;
        }

        // final fallback: query DB directly
        $data = $this->queryIndex($page, $per);
        Cache::put($cacheKey, $data, $ttl);
        return $data;
    }

    /**
     * DB query for index (kept separate for testability).
     */
    protected function queryIndex(int $page, int $per)
    {
        $offset = ($page - 1) * $per;

        return DB::table('events')
            ->select('id', 'name', 'start_date', 'end_date', 'capacity')
            ->orderBy('start_date')
            ->offset($offset)
            ->limit($per)
            ->get();
    }

    /**
     * Invalidate a specific page (or all pages) after changes.
     */
    public function invalidateIndexCache(int $page = null, int $per = null): void
    {
        if ($page && $per) {
            Cache::forget("{$this->indexCachePrefix}:p{$page}:n{$per}");
            return;
        }

        // naive purge: you can improve with tags or tracking keys
        // here we remove first few pages commonly used
        for ($p = 1; $p <= 5; $p++) {
            Cache::forget("{$this->indexCachePrefix}:p{$p}:n{$per}");
        }
    
     }
}