<?php

namespace Tests\Feature\Mutations\BackOffice\Users;

use App\Enums\Permissions\UserRolesEnum;
use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\BackOffice\Users\UserCreateMutation;
use App\Models\Branches\Branch;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Models\Phones\Phone;
use App\Models\Users\User;
use App\Models\Users\UserBranch;
use App\Notifications\Users\SendPasswordNotification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        Notification::fake();
    }

    public function test_create_user(): void
    {
        $branch = Branch::factory()
            ->create();

        $role = Role::whereName(UserRolesEnum::INSPECTOR)
            ->first();

        $user = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_name' => $this->faker->lastName,
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone,
                ]
            ],
            'email' => $this->faker->safeEmail,
            'branch_id' => $branch->id,
            'role_id' => $role->id
        ];

        $userId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(UserCreateMutation::NAME)
                ->args(
                    [
                        'user' => $user
                    ]
                )
                ->select(
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'second_name',
                        'phone',
                        'phones' => [
                            'is_default',
                            'phone'
                        ],
                        'email',
                        'branch' => [
                            'id',
                            'name',
                            'city',
                            'region' => [
                                'id',
                                'slug'
                            ],
                            'address'
                        ],
                        'role' => [
                            'id',
                            'name',
                            'permissions'
                        ],
                        'language' => [
                            'name',
                            'slug',
                        ],
                        'inspections_count'
                    ]
                )
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        UserCreateMutation::NAME => [
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name'],
                            'second_name' => $user['second_name'],
                            'phone' => $user['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $user['phones'][0]['phone'],
                                    'is_default' => true
                                ]
                            ],
                            'email' => $user['email'],
                            'branch' => [
                                'id' => $branch->id,
                                'name' => $branch->name,
                                'city' => $branch->city,
                                'region' => [
                                    'id' => $branch->region_id,
                                    'slug' => $branch->region->slug
                                ],
                                'address' => $branch->address,
                            ],
                            'role' => [
                                'id' => $role->id,
                                'name' => $role->name,
                                'permissions' => $role->permissions->pluck('name')
                                    ->toArray()
                            ],
                            'language' => [
                                'name' => Language::whereSlug(config('app.locale'))
                                    ->first()->name,
                                'slug' => config('app.locale'),
                            ],
                            'inspections_count' => null
                        ]
                    ]
                ]
            )
            ->json('data.' . UserCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            User::class,
            [
                'id' => $userId
            ]
        );

        Notification::assertSentTo(User::find($userId), SendPasswordNotification::class);

        $this->assertDatabaseHas(
            UserBranch::class,
            [
                'user_id' => $userId,
                'branch_id' => $branch->id
            ]
        );

        $this->assertDatabaseHas(
            Phone::class,
            [
                'owner_type' => MorphModelNameEnum::user()->key,
                'owner_id' => $userId
            ]
        );
    }

    public function test_create_user_without_branch(): void
    {
        $role = Role::whereName(UserRolesEnum::INSPECTOR)
            ->first();

        $user = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_name' => $this->faker->lastName,
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone
                ]
            ],
            'email' => $this->faker->safeEmail,
            'role_id' => $role->id
        ];

        $userId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(UserCreateMutation::NAME)
                ->args(
                    [
                        'user' => $user
                    ]
                )
                ->select(
                    [
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
                        'branch' => [
                            'id',
                        ],
                        'role' => [
                            'id',
                            'name',
                            'permissions'
                        ],
                        'language' => [
                            'name',
                            'slug',
                        ]
                    ]
                )
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        UserCreateMutation::NAME => [
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name'],
                            'second_name' => $user['second_name'],
                            'phone' => $user['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $user['phones'][0]['phone'],
                                    'is_default' => true
                                ]
                            ],
                            'email' => $user['email'],
                            'branch' => null,
                            'role' => [
                                'id' => $role->id,
                                'name' => $role->name,
                                'permissions' => $role->permissions->pluck('name')
                                    ->toArray()
                            ],
                            'language' => [
                                'name' => Language::whereSlug(config('app.locale'))
                                    ->first()->name,
                                'slug' => config('app.locale'),
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . UserCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            User::class,
            [
                'id' => $userId
            ]
        );

        Notification::assertSentTo(User::find($userId), SendPasswordNotification::class);
    }

    public function test_try_create_user_with_not_unique_email(): void
    {
        $user = User::factory()
            ->inspector()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(UserCreateMutation::NAME)
                ->args(
                    [
                        'user' => [
                            'first_name' => $this->faker->firstName,
                            'last_name' => $this->faker->lastName,
                            'second_name' => $this->faker->lastName,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone
                                ]
                            ],
                            'email' => $user->email,
                            'branch_id' => Branch::factory()
                                ->create()->id,
                            'role_id' => Role::whereName(UserRolesEnum::INSPECTOR)
                                ->first()->id
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.users.uniq_email')
                        ]
                    ]
                ]
            );
    }
}
