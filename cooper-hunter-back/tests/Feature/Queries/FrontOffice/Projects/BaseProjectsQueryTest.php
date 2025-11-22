<?php

namespace Tests\Feature\Queries\FrontOffice\Projects;

use App\Contracts\Members\Member;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class BaseProjectsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected function assertMemberCanViewProjects(Member|User|Technician $member): void
    {
        $this->createProjects($member);

        $query = $this->getGraphQLQuery();

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonCount(5, 'data.' . static::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        $this->assertDatabaseCount(Project::TABLE, 10);
    }

    protected function createProjects(User|Technician|Member $member, int $count = 5): Collection|Project
    {
        Project::factory()
            ->times($count)
            ->has(
                System::factory()
                    ->hasAttached(
                        Product::factory(),
                        fn() => ['serial_number' => $this->faker->unique->randomNumber()],
                        relationship: 'units'
                    )
            )
            ->create();

        return Project::factory()
            ->times($count)
            ->for($member, 'member')
            ->has(
                System::factory()
                    ->hasAttached(
                        Product::factory(),
                        fn($a) => ['serial_number' => $this->faker->unique->randomNumber()],
                        relationship: 'units'
                    )
            )
            ->create();
    }

    protected function getGraphQLQuery(array $args = []): GraphQLQuery
    {
        return new GraphQLQuery(
            static::QUERY,
            $args,
            [
                'data' => [
                    'id',
                    'name',
                    'systems' => [
                        'name',
                        'description',
                        'warranty_status',
                        'units' => [
                            'id',
                            'serial_number',
                            'tickets_exists',
                        ],
                    ],
                ]
            ]
        );
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                static::QUERY => [
                    'data' => [
                        [
                            'id',
                            'name',
                            'systems' => [
                                [
                                    'name',
                                    'description',
                                    'warranty_status',
                                    'units' => [
                                        [
                                            'id',
                                            'serial_number'
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }

    protected function assertMemberCanViewProjectById(Member|User|Technician $member): void
    {
        $project = $this->createProjects($member)->first();

        $query = $this->getGraphQLQuery(
            [
                'id' => $project->id
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonCount(1, 'data.' . static::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }
}
