<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_delete_admin(): void
    {
        $this->loginAsAdminWithRole();

        $admin = Admin::factory()
            ->withRole(AdminRolesEnum::ADMIN)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $admin->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Admin::class,
            [
                'id' => $admin->id
            ]
        );

        $this->assertDatabaseMissing(
            Phone::class,
            [
                'owner_type' => MorphModelNameEnum::admin()->key,
                'owner_id' => $admin->id
            ]
        );
    }

    public function test_try_to_delete_super_admin(): void
    {
        $this->loginAsAdminWithRole();

        $admin = Admin::factory()
            ->withRole()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $admin->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.admins.can_not_delete_super_admin')
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $admin->id
            ]
        );

        $this->assertDatabaseHas(
            Phone::class,
            [
                'owner_type' => MorphModelNameEnum::admin()->key,
                'owner_id' => $admin->id
            ]
        );
    }
}
