<?php

namespace Tests\Feature\Queries\BackOffice\Projects;

use App\Enums\Users\UserMorphEnum;
use App\GraphQL\Queries\BackOffice\Projects\ProjectsQuery;
use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\ProjectCreateTrait;

class ProjectsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;

    public const QUERY = ProjectsQuery::NAME;

    public function test_get_projects(): void
    {
        $this->loginAsSuperAdmin();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ]
        );

        Project::factory()
            ->times(10)
            ->create();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(10, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function getQuery(array $select, array $args = []): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select($select)
            ->make();
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                self::QUERY => [
                    'data' => [
                        [
                            'id'
                        ]
                    ],
                ],
            ]
        ];
    }

    public function test_get_projects_by_user(): void
    {
        $this->loginAsSuperAdmin();

        Project::factory()
            ->times(10)
            ->create();

        Project::factory()
            ->for(
                $user = User::factory()
                    ->create(),
                'member'
            )
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'user_id' => $user->id
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    public function test_get_projects_by_name(): void
    {
        $this->loginAsSuperAdmin();

        $projects = Project::factory()
            ->times(2)
            ->create();

        $projects[0]->name = $this->faker->unique->lexify;
        $projects[1]->name = $this->faker->unique->lexify;

        $projects[0]->save();
        $projects[1]->save();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'name' => $projects[0]->name
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $projects[0]->id
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_by_member_user_type(): void
    {
        $this->loginAsSuperAdmin();

        Project::factory()
            ->times(10)
            ->create();

        Project::factory()
            ->times(5)
            ->for(
                Technician::factory()
                    ->create(),
                'member'
            )
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_type' => UserMorphEnum::USER()
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(10, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    public function test_get_projects_by_member_technician_type(): void
    {
        $this->loginAsSuperAdmin();

        Project::factory()
            ->times(10)
            ->create();

        Project::factory()
            ->times(5)
            ->for(
                Technician::factory()
                    ->create(),
                'member'
            )
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_type' => UserMorphEnum::TECHNICIAN()
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    public function test_get_projects_by_member_user_name(): void
    {
        $this->loginAsSuperAdmin();

        $user = User::factory()
            ->create(['last_name' => $this->faker->unique->name]);
        $technician = Technician::factory()
            ->create(['last_name' => $this->faker->unique->name]);

        $userProject = Project::factory()
            ->for($user, 'member')
            ->create();

        Project::factory()
            ->for($technician, 'member')
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_name' => $user->last_name
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $userProject->id
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_by_member_technician_name(): void
    {
        $this->loginAsSuperAdmin();

        $user = User::factory()
            ->create(['last_name' => $this->faker->unique->name]);
        $technician = Technician::factory()
            ->create(['last_name' => $this->faker->unique->name]);

        Project::factory()
            ->for($user, 'member')
            ->create();

        $technicianProject = Project::factory()
            ->for($technician, 'member')
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_name' => $technician->last_name
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $technicianProject->id
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_by_member_name(): void
    {
        $this->loginAsSuperAdmin();

        $name = $this->faker->name;

        $user = User::factory()
            ->create(['last_name' => $name]);
        $technician = Technician::factory()
            ->create(['last_name' => $name]);

        $userProject = Project::factory()
            ->for($user, 'member')
            ->create(
                [
                    'created_at' => Carbon::now()
                        ->subDay()
                        ->toDateTimeString()
                ]
            );

        $technicianProject = Project::factory()
            ->for($technician, 'member')
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_name' => $name
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(2, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $technicianProject->id
                                ],
                                [
                                    'id' => $userProject->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_by_member_email(): void
    {
        $this->loginAsSuperAdmin();

        $user = User::factory()
            ->create(['last_name' => $this->faker->unique->email]);
        $technician = Technician::factory()
            ->create(['last_name' => $this->faker->unique->email]);

        $userProject = Project::factory()
            ->for($user, 'member')
            ->create();

        Project::factory()
            ->for($technician, 'member')
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_email' => $user->email
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $userProject->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_by_member_id(): void
    {
        $this->loginAsSuperAdmin();

        $user = User::factory()
            ->create(['id' => 1]);
        $technician = Technician::factory()
            ->create(['id' => 1]);

        $userProject = Project::factory()
            ->for($user, 'member')
            ->create(
                [
                    'created_at' => Carbon::now()
                        ->subDay()
                        ->toDateTimeString()
                ]
            );

        $technicianProject = Project::factory()
            ->for($technician, 'member')
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_id' => 1
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(2, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $technicianProject->id
                                ],
                                [
                                    'id' => $userProject->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_by_combine_filter(): void
    {
        $this->loginAsSuperAdmin();

        $user = User::factory()
            ->create(
                [
                    'id' => 1,
                    'last_name' => $this->faker->unique->lastName,
                    'email' => $this->faker->unique->email
                ]
            );

        $technician = Technician::factory()
            ->create(
                [
                    'id' => 1,
                    'last_name' => $this->faker->unique->lastName,
                    'email' => $this->faker->unique->email
                ]
            );

        $userProject = Project::factory()
            ->for($user, 'member')
            ->create();

        Project::factory()
            ->for($technician, 'member')
            ->create();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'member_id' => 1,
                'member_name' => $user->last_name,
                'member_type' => UserMorphEnum::USER()
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $userProject->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_by_serial_number(): void
    {
        $this->loginAsSuperAdmin();

        $technician = Technician::factory()
            ->create(['id' => 1]);

        $technicianProject = $this->createProjectForMember($technician);
        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ],
            [
                'serial_number' => $technicianProject->systems[0]->units[0]->unit->serial_number
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $technicianProject->id
                                ],
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_projects_for_soft_deleted_member_success(): void
    {
        $this->loginAsSuperAdmin();

        $query = $this->getQuery(
            [
                'data' => [
                    'id'
                ]
            ]
        );

        Project::factory()
            ->for(User::factory()->deleted(), 'member')
            ->create();

        Project::factory()
            ->for(Technician::factory()->deleted(), 'member')
            ->create();

        Project::factory()
            ->for(User::factory(), 'member')
            ->create();

        Project::factory()
            ->for(Technician::factory(), 'member')
            ->create();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(2, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }
}
