<?php

namespace Tests\Traits\Models;

use App\Contracts\Members\Member;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;

trait ProjectCreateTrait
{
    use WithFaker;

    protected function createProjectForMember(Member $member): Project
    {
        /** @var Model $member */

        $serial = $this->faker->lexify;

        return Project::factory()
            ->for($member, 'member')
            ->has(
                System::factory()
                    ->hasAttached(
                        Product::factory()
                            ->has(
                                ProductSerialNumber::factory()
                                    ->state(
                                        ['serial_number' => $serial]
                                    ),
                                'serialNumbers'
                            ),
                        fn($a) => ['serial_number' => $serial],
                        relationship: 'units'
                    )
            )
            ->create();
    }
}
