<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteProfileMutation;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class AdminDeleteProfileMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = AdminDeleteProfileMutation::NAME;

    public function test_delete_profile(): void
    {
        $admin = $this->loginAsAdmin()->assignRole(
            $this->generateRole(
                'Admin can delete profile',
                [
                    AdminDeletePermission::KEY,
                ],
                Admin::GUARD
            )
        );

        $query = new GraphQLQuery(self::MUTATION);

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertDatabaseMissing(
            Admin::TABLE,
            [
                'id' => $admin->id
            ],
        );
    }

    public function test_super_cannot_delete_himself(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(self::MUTATION);

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJsonCount(1, 'errors');

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $admin->id
            ],
        );
    }
}
