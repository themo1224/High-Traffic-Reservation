<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $per = (int) $request->query('per', 15);
        // ttl / lock query params are no longer required by the service — keep API simple
        $data = $this->events->index($per, $page);

        return response()->json($data);
    }

    public function invalidateCache(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per' => 'nullable|integer|min:1',
        ]);

        $page = $validated['page'] ?? null;
        $per = $validated['per'] ?? null;

        $this->events->invalidateIndexCache($page, $per);

        return response()->json(['ok' => true], 200);
    }
}
