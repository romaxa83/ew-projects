<?php

namespace Database\Seeders\Demo;

use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class BaseDemoSeeder extends Seeder
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Container::getInstance()->make(Generator::class);
    }
}
