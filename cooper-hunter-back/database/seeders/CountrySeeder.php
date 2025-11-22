<?php

namespace Database\Seeders;

use App\Console\Commands\Import\CountryAndState;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call(CountryAndState::class);
    }
}
