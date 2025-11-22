<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\BackOffice\Admins\AdminCreateMutation;
use App\Models\Admins\Admin;
use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Models\Phones\Phone;
use App\Notifications\Admins\SendPasswordNotification;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_create_admin(): void
    {
        $this->loginAsAdminWithRole();

        $role = Role::whereName(AdminRolesEnum::ADMIN)
            ->first();

        $adminData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'second_name' => $this->faker->firstName,
            'phones' => [
                [
                    'phone' => $this->faker->ukrainianPhone
                ]
            ],
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->bothify("##??##??##"),
            'role_id' => $role->id
        ];

        $adminId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminCreateMutation::NAME)
                ->args(
                    [
                        'admin' => $adminData
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
                        'role' => [
                            'id',
                            'name',
                            'permissions'
                        ],
                        'language' => [
                            'name',
                            'slug'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminCreateMutation::NAME => [
                            'first_name' => $adminData['first_name'],
                            'last_name' => $adminData['last_name'],
                            'second_name' => $adminData['second_name'],
                            'phone' => $adminData['phones'][0]['phone'],
                            'phones' => [
                                [
                                    'phone' => $adminData['phones'][0]['phone'],
                                    'is_default' => true
                                ]
                            ],
                            'email' => $adminData['email'],
                            'role' => [
                                'id' => $role->id,
                                'name' => $role->name,
                                'permissions' => $role->permissions->pluck('name')
                                    ->toArray()
                            ],
                            'language' => [
                                'name' => Language::default()
                                    ->first()->name,
                                'slug' => Language::default()
                                    ->first()->slug,
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . AdminCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $adminId
            ]
        );

        $admin = Admin::find($adminId);

        Notification::assertSentTo(
            $admin,
            SendPasswordNotification::class
        );

        $this->assertDatabaseHas(
            Phone::class,
            [
                'owner_id' => $adminId,
                'owner_type' => MorphModelNameEnum::admin()->key,
                'phone' => $adminData['phones'][0]['phone'],
                'is_default' => true
            ]
        );

        $this->assertTrue(Hash::check($adminData['password'], $admin->password));
    }

    public function test_try_to_create_admin_by_admin(): void
    {
        $this->loginAsAdmin(
            Admin::factory()
                ->withRole(AdminRolesEnum::ADMIN)
                ->create()
        );

        $role = Role::whereName(AdminRolesEnum::ADMIN)
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminCreateMutation::NAME)
                ->args(
                    [
                        'admin' => [
                            'first_name' => $this->faker->firstName,
                            'last_name' => $this->faker->lastName,
                            'second_name' => $this->faker->firstName,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone
                                ]
                            ],
                            'email' => $this->faker->safeEmail,
                            'password' => $this->faker->bothify("##??##??##"),
                            'role_id' => $role->id
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
                            'message' => AuthorizationMessageEnum::NO_PERMISSION
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_create_similar_admin(): void
    {
        $admin = $this->loginAsAdminWithRole();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminCreateMutation::NAME)
                ->args(
                    [
                        'admin' => [
                            'first_name' => $this->faker->firstName,
                            'last_name' => $this->faker->lastName,
                            'second_name' => $this->faker->firstName,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone,
                                ]
                            ],
                            'email' => $admin->email,
                            'role_id' => $admin->role->id
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
                            'message' => trans(
                                'validation.custom.admins.uniq_email',
                                ['admin' => $admin->getName()]
                            )
                        ]
                    ]
                ]
            );
    }

    public function test_create_admin_without_password(): void
    {
        $admin = $this->loginAsAdminWithRole();

        $adminId = $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminCreateMutation::NAME)
                ->args(
                    [
                        'admin' => [
                            'first_name' => $this->faker->firstName,
                            'last_name' => $this->faker->lastName,
                            'second_name' => $this->faker->firstName,
                            'phones' => [
                                [
                                    'phone' => $this->faker->ukrainianPhone
                                ]
                            ],
                            'email' => $this->faker->safeEmail,
                            'role_id' => $admin->role->id
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
            ->assertJsonStructure(
                [
                    'data' => [
                        AdminCreateMutation::NAME => [
                            'id'
                        ]
                    ]
                ]
            )
            ->json('data.' . AdminCreateMutation::NAME . '.id');

        $this->assertNotNull(Admin::find($adminId)->password);
    }
}
