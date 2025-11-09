<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Models\Ticket::class;

    public function definition(): array
    {
        // Generic/random ticket — useful for standalone seeding
        $seatNumber = $this->faker->numberBetween(1, 500);
        $seatRow = (int) ceil($seatNumber / 10);

        return [
            'ticket_number' => $seatNumber,
            'seat_row'      => $seatRow,
            'seat_number'   => ($seatNumber % 10) === 0 ? 10 : $seatNumber % 10,
            'event_id'      => \App\Models\Event::inRandomOrder()->first()->id ?? 1,
            'user_id'       => null,
            'status'        => 'available',
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    
    }
}
