<?php

/** @var Factory $factory */

//use Faker\Generator as Faker;
use App\Models\Logs\Log as LogModel;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    LogModel::class,
    function () {
        return [
            'message' => 'Some message',
            'channel' => 'stack',
            'level' => LogModel::NOTICE,
            'level_name' => LogModel::NOTICE_NAME,
            'unix_time' => now()->getTimestamp(),
            'datetime' => now(),
            'context' => 'Some context',
            'extra' => 'Some text',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
);

