<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Redis;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::factory()->count(5)->create()->each(function (Event $event) {
            // Initialize Redis seat counter for AP-mode (if Redis configured)
            try {
                $key = "event:{$event->id}:seats";
                Redis::set($key, $event->capacity);
            } catch (\Throwable $e) {
                // Redis might not be available — ignore
            }
        });
    }
}
