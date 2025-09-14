<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numberBetween(1, 17),
            'name' => 'Region ' . $this->faker->numberBetween(1, 17),
            'latitude' => $this->faker->latitude(4, 21),
            'longitude' => $this->faker->longitude(116, 126),
            'order' => $this->faker->numberBetween(1, 17),
        ];
    }
}
