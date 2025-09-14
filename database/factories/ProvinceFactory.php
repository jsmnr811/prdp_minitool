<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Province>
 */
class ProvinceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numberBetween(100000, 999999),
            'name' => $this->faker->city() . ' Province',
            'region_code' => $this->faker->numberBetween(1, 17),
            'latitude' => $this->faker->latitude(4, 21),
            'longitude' => $this->faker->longitude(116, 126),
        ];
    }
}
