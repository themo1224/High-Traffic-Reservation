<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $event) {
            // Skip if tickets already exist for this event
            if (Ticket::where('event_id', $event->id)->exists()) {
                continue;
            }

            // Create tickets in a transaction for safety
            DB::transaction(function() use ($event) {
                $capacity = max(0, (int)$event->capacity);

                for ($i = 1; $i <= $capacity; $i++) {
                    $seatRow = (int) ceil($i / 10);
                    $seatNumber = ($i % 10) === 0 ? 10 : $i % 10;

                    Ticket::create([
                        'ticket_number' => $i,
                        'seat_row'      => $seatRow,
                        'seat_number'   => $seatNumber,
                        'event_id'      => $event->id,
                        'user_id'       => null,
                        'status'        => 'available',
                    ]);
                }
            });
        }
    }
}
