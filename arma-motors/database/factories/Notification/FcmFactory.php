<?php

namespace Database\Factories\Notification;

use App\Models\Notification\Fcm;
use App\Services\Firebase\FcmAction;
use Illuminate\Database\Eloquent\Factories\Factory;

class FcmFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Fcm::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'status' => Fcm::STATUS_CREATED,
            'action' => FcmAction::ACTION_TEST,
            'send_data' => [
                'title' => $this->faker->sentence,
                'body' => $this->faker->sentence
            ],
            'type' => Fcm::TYPE_NEW,
        ];
    }

    public function hasError()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Fcm::STATUS_HAS_ERROR,
            ];
        });
    }

    public function send()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Fcm::STATUS_SEND,
            ];
        });
    }
}
