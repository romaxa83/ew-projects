<?php

namespace Tests\Feature\Queries\BackOffice\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use App\GraphQL\Queries\BackOffice\Admins\AdminsQuery;
use App\Models\Admins\Admin;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminsQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $admins;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        Admin::factory()
            ->withRole(AdminRolesEnum::ADMIN)
            ->count(5)
            ->create();

        $this->admins = Admin::all();
    }

    public function test_get_all_admins(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AdminsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'first_name',
                            'last_name',
                            'second_name',
                            'phone',
                            'phones' => [
                                'phone',
                                'is_default'
                            ],
                            'email',
                            'language' => [
                                'name',
                                'slug',
                            ],
                            'role' => [
                                'id',
                                'name',
                                'permissions'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        AdminsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'first_name',
                                    'last_name',
                                    'second_name',
                                    'phone',
                                    'phones' => [
                                        '*' => [
                                            'phone',
                                            'is_default'
                                        ]
                                    ],
                                    'email',
                                    'language' => [
                                        'name',
                                        'slug',
                                    ],
                                    'role' => [
                                        'id',
                                        'name',
                                        'permissions'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(count($this->admins), 'data.' . AdminsQuery::NAME . '.data');
    }

    public function test_default_sorting(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AdminsQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminsQuery::NAME => [
                            'data' => $this->admins
                                ->sortByDesc('created_at')
                                ->values()
                                ->map(
                                    fn(Admin $admin) => [
                                        'id' => $admin->id
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_name_sorting(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AdminsQuery::NAME)
                ->args(
                    [
                        'sort' => [
                            'full_name-asc'
                        ]
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminsQuery::NAME => [
                            'data' => $this->admins
                                ->sortBy('last_name')
                                ->values()
                                ->map(
                                    fn(Admin $admin) => [
                                        'id' => $admin->id
                                    ]
                                )
                                ->toArray()
                        ]
                    ]
                ]
            );
    }

    public function test_filter_by_email(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AdminsQuery::NAME)
                ->args(
                    [
                        'query' => $this->admins[2]->email
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->admins[2]->id
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_filter_by_phone(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(AdminsQuery::NAME)
                ->args(
                    [
                        'query' => $this->admins[3]->phone->phone
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminsQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->admins[3]->id
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }
}
