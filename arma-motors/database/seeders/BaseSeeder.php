<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class BaseSeeder extends Seeder
{
    private $faker;

    public function __construct()
    {
        $this->faker = resolve(Faker::class);
    }

    protected function getFaker()
    {
        return $this->faker;
    }
}
