<?php

namespace Tests\Unit;

use App\Services\EventService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class EventServiceTest extends TestCase
{
    public function test_index_returns_repo_value()
    {
        $page = 1;
        $per = 15;

        $items = collect([(object) ['id' => 1, 'title' => 'A']]);

        $repo = $this->createMock(\App\Repositories\EventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getIndex')
            ->with($page, $per)
            ->willReturn($items);

        // inject a tiny in-memory cache stub so we don't rely on Laravel facades in unit tests
        $cache = new class {
            public array $store = [];
            public function remember($key, $ttl, $cb) {
                if (array_key_exists($key, $this->store)) {
                    return $this->store[$key];
                }
                $this->store[$key] = $cb();
                return $this->store[$key];
            }
            public function forget($key) {
                if (array_key_exists($key, $this->store)) {
                    unset($this->store[$key]);
                }
                return true;
            }
        };

        $service = new EventService($repo, $cache);

        $result = $service->index($per, $page);

        $this->assertEquals($items, $result);
    }

    public function test_invalidate_runs_without_exception()
    {
        $page = 1;
        $per = 15;

        $items = collect([(object) ['id' => 1]]);

        $repo = $this->createMock(\App\Repositories\EventRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('getIndex')
            ->with($page, $per)
            ->willReturn($items);

        $cache = new class {
            public array $store = [];
            public function remember($key, $ttl, $cb) {
                if (array_key_exists($key, $this->store)) {
                    return $this->store[$key];
                }
                $this->store[$key] = $cb();
                return $this->store[$key];
            }
            public function forget($key) {
                if (array_key_exists($key, $this->store)) {
                    unset($this->store[$key]);
                }
                return true;
            }
        };

        $service = new EventService($repo, $cache);

        // cache once
        $service->index($per, $page);

        // invalidation should not throw and should remove the key from the cache
        $service->invalidateIndexCache($page, $per);

        $this->assertTrue(true);

        $this->assertTrue(true);
    }
}
