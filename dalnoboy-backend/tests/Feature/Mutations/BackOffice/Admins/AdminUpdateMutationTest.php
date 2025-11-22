<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Models\Phones\Phone;
use App\Notifications\Admins\ChangePasswordNotification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_update_admin(): void
    {
        $this->loginAsAdminWithRole();

        $admin = Admin::factory()
            ->withRole()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $admin->id,
                        'admin' => [
                            'first_name' => $admin->first_name,
                            'last_name' => $admin->last_name,
                            'second_name' => $admin->second_name,
                            'phones' => [
                                [
                                    'phone' => $phone = $this->faker->ukrainianPhone
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
                        'phone',
                        'phones' => [
                            'phone',
                            'is_default'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminUpdateMutation::NAME => [
                            'id' => $admin->id,
                            'phone' => $phone,
                            'phones' => [
                                [
                                    'phone' => $phone,
                                    'is_default' => true
                                ]
                            ]
                        ]
                    ]
                ]
            );

        Notification::assertNotSentTo(
            $admin,
            ChangePasswordNotification::class
        );
    }

    public function test_update_admin_password(): void
    {
        $this->loginAsAdminWithRole();

        $admin = Admin::factory()
            ->withRole()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $admin->id,
                        'admin' => [
                            'first_name' => $admin->first_name,
                            'last_name' => $admin->last_name,
                            'second_name' => $admin->second_name,
                            'phones' => $admin
                                ->phones
                                ->map(
                                    fn(Phone $phone) => [
                                        'phone' => $phone->phone,
                                        'is_default' => $phone->is_default
                                    ]
                                )
                                ->toArray(),
                            'email' => $admin->email,
                            'password' => $password = $this->faker->bothify("##??##??##"),
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
                    'data' => [
                        AdminUpdateMutation::NAME => [
                            'id' => $admin->id,
                        ]
                    ]
                ]
            );

        Notification::assertSentTo(
            $admin,
            ChangePasswordNotification::class
        );

        $this->assertTrue(Hash::check($password, $admin->refresh()->password));
    }

    public function test_update_admin_role(): void
    {
        $this->loginAsAdminWithRole();

        $admin = Admin::factory()
            ->withRole()
            ->create();

        $role = Role::whereName(AdminRolesEnum::ADMIN)
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $admin->id,
                        'admin' => [
                            'first_name' => $admin->first_name,
                            'last_name' => $admin->last_name,
                            'second_name' => $admin->second_name,
                            'phones' => $admin
                                ->phones
                                ->map(
                                    fn(Phone $phone) => [
                                        'phone' => $phone->phone,
                                        'is_default' => $phone->is_default
                                    ]
                                )
                                ->toArray(),
                            'email' => $admin->email,
                            'role_id' => $role->id
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'role' => [
                            'id',
                            'name',
                            'permissions'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminUpdateMutation::NAME => [
                            'id' => $admin->id,
                            'role' => [
                                'id' => $role->id,
                                'name' => $role->name,
                                'permissions' => $role->permissions->pluck('name')
                                    ->toArray()
                            ]
                        ]
                    ]
                ]
            );
    }
}
