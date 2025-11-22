<?php
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Saas\Support\SupportRequestMessage;
/** @var Factory $factory */

$factory->define(
    SupportRequest::class,
    function (Faker $faker) {
        return [
            'user_id' => null,
            'carrier_id' => null,
            'admin_id' => null,
            'user_name' => $faker->name,
            'user_email' => $faker->email,
            'user_phone' => $faker->e164PhoneNumber,
            'subject' => $faker->text(100),
            'label' => $faker->numberBetween(0,3),
            'status' => 1
        ];
    }
);

$factory->define(
    SupportRequestMessage::class,
    function (Faker $faker) {
        return [
            'support_request_id' => null,
            'message' => $faker->text,
            'is_read' => false,
            'is_user_message' => true
        ];
    }
);
