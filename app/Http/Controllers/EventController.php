<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    protected EventService $events;

    public function __construct(EventService $events)
    {
        $this->events = $events;
    }

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $per  = (int) $request->query('per', 15);
        $ttl  = (int) $request->query('ttl', 15);
        $lock = (int) $request->query('lock', 10);

        $data = $this->events->index($per, $page, $ttl, $lock);

        return response()->json($data);
    }

    public function invalidateCache(Request $request): JsonResponse
    {
        $page = $request->input('page');
        $per  = $request->input('per');

        $this->events->invalidateIndexCache($page ? (int)$page : null, $per ? (int)$per : null);

        return response()->json(['ok' => true]);
    }
}
