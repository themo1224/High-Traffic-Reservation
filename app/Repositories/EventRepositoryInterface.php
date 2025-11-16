<?php

namespace App\Repositories;

interface EventRepositoryInterface
{
    /**
     * Return a page of events (stdClass collection).
     *
     * @param int $page
     * @param int $per
     * @return \Illuminate\Support\Collection
     */
    public function getIndex(int $page, int $per);
}
