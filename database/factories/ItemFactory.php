<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'itemName' => $this->faker->word,
            'itemCode' => $this->faker->word,
            'isAvailable' => $this->faker->boolean,
            'isDeleted' => $this->faker->boolean,
        ];
    }
}
