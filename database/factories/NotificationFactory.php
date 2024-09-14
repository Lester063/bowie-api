<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition()
    {
        return [
            'recipientUserId' => $this->faker->numberBetween(1, 10), // Adjust as needed
            'senderUserId' => $this->faker->numberBetween(1, 10), // Adjust as needed
            'type' => $this->faker->word,
            'notificationMessage' => $this->faker->sentence,
            'isRead' => $this->faker->boolean,
            'typeValueId' => $this->faker->numberBetween(1, 100),
        ];
    }
}
