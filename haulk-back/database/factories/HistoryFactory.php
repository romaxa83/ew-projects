<?php

/** @var Factory $factory */

//use Faker\Generator as Faker;
use App\Models\History\History;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    History::class,
    function () {
        return [
            'message' => 'Some message',
            'performed_at' => now(),
        ];
    }
);

