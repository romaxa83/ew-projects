<?php

namespace Database\Seeders\Catalog\Troubleshoots;

use App\Models\Catalog\Troubleshoots\Group;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use Illuminate\Database\Seeder;

class TroubleshootSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Group::get() as $item){
            Troubleshoot::factory()
                ->times(10)
                ->state([
                    'group_id' => $item->id
                ])
                ->create();
        }
    }
}

