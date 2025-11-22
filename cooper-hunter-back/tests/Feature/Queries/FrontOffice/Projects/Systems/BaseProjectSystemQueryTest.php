<?php

namespace Tests\Feature\Queries\FrontOffice\Projects\Systems;

use App\Contracts\Members\Member;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class BaseProjectSystemQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected function getGraphQLQuery(array $args = []): array
    {
        return GraphQLQuery::query(static::QUERY)
            ->args($args)
            ->select(
                [
                    'id',
                    'name',
                    'description',
                    'warranty_status',
                    'units' => [
                        'id',
                        'serial_number'
                    ],
                ]
            )
            ->make();
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                static::QUERY => [
                    'id',
                    'name',
                    'description',
                    'warranty_status',
                    'units' => [
                        [
                            'id',
                            'serial_number'
                        ]
                    ],
                ],
            ],
        ];
    }

    protected function createSystemForMember(Member $member): System
    {
        /** @var Model|Member $member */

        return System::factory()
            ->for(
                Project::factory()
                    ->for($member, 'member')
            )
            ->hasAttached(
                Product::factory(),
                fn() => ['serial_number' => $this->faker->unique->randomNumber()],
                relationship: 'units'
            )
            ->create();
    }
}
