<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DbEventRepository implements EventRepositoryInterface
{
    public function getIndex(int $page, int $per)
    {
        $offset = ($page - 1) * $per;

        return DB::table('events')
            ->select('id', 'name', 'start_date', 'end_date', 'capacity')
            ->orderBy('start_date')
            ->offset($offset)
            ->limit($per)
            ->get();
    }
}
