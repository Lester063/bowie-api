<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Returns>
 */
class ReturnsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idRequest' => $this->faker->numberBetween(1, 10),
            'idReturner' => $this->faker->numberBetween(1, 10),
            'isApprove' => $this->faker->boolean,
        ];
    }
}
