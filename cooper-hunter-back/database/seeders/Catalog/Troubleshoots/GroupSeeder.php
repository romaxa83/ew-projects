<?php

namespace Database\Seeders\Catalog\Troubleshoots;

use App\Models\Catalog\Troubleshoots\GroupTranslation;
use App\Models\Catalog\Troubleshoots\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        Group::factory()->times(10)
            ->has(
            GroupTranslation::factory()
                ->times(2)
                ->sequence(
                    ['language' => 'en'],
                    ['language' => 'es'],
                ),
            'translations'
        )
            ->create();
    }
}

