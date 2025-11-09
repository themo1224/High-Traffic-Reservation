<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);
        $start = $this->faker->dateTimeBetween('+1 days', '+90 days');
        $end = (clone $start)->modify('+3 hours');

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100,999),
            'start_date' => $start,
            'end_date' => $end,
            // timestamp column in your migration — fill it with created_at or a derived value
            'timestamp' => $start,
            'capacity' => $this->faker->numberBetween(50, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
