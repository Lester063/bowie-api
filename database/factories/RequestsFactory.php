<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requests>
 */
class RequestsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idRequester' => $this->faker->numberBetween(1, 10),
            'idItem' => $this->faker->numberBetween(1, 10),
            'statusRequest' => $this->faker->word,
            'isReturnSent' => $this->faker->boolean
        ];
    }
}
