<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateProfileMutation;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class AdminUpdateProfileMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = AdminUpdateProfileMutation::NAME;

    public function test_admin_can_update_own_profile(): void
    {
        $admin = $this->loginAsAdmin();
        $admin->assignRole(
            $this->generateRole(
                'admin member',
                [AdminUpdatePermission::KEY],
                Admin::GUARD
            )
        );

        $query = sprintf(
            'mutation {
                %s (
                    name: "%s",
                    email: "%s"
                ) {
                    name
                    email
                }
            }',
            self::MUTATION,
            $name = 'new admin name',
            $email = 'new-admin@email.com',
        );

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $admin->refresh();

        self::assertSame(
            compact('name', 'email'),
            [
                'name' => $admin->name,
                'email' => (string)$admin->email,
            ],
        );
    }
}
