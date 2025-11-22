<?php

namespace Tests\Feature\Mutations\BackOffice\Users;

use App\GraphQL\Mutations\BackOffice\Users\UserDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Permissions\Users\UserDeletePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class UserDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const MUTATION = UserDeleteMutation::NAME;

    public function test_cant_delete_user_by_other_user(): void
    {
        $this->loginAsUser();

        $user = User::factory()->create();

        $result = $this->query($user->id);

        $this->assertGraphQlUnauthorized($result);
        $this->assertDatabaseHas(User::TABLE, ['id' => $user->id]);
    }

    protected function query(int $id): TestResponse
    {
        $query = sprintf(
            'mutation { %s (ids: [%s]) }',
            self::MUTATION,
            $id,
        );

        return $this->postGraphQLBackOffice(['query' => $query]);
    }

    public function test_cant_delete_user_by_guest(): void
    {
        $user = User::factory()->create();

        $result = $this->query($user->id);

        $this->assertGraphQlUnauthorized($result);
        $this->assertDatabaseHas(User::TABLE, ['id' => $user->id]);
    }

    public function test_cant_delete_user_by_admin_without_permission(): void
    {
        $this->loginAsAdmin();

        $user = User::factory()->create();

        $result = $this->query($user->id);

        $this->assertGraphQlUnauthorized($result);
        $this->assertDatabaseHas(User::TABLE, ['id' => $user->id]);
    }

    public function test_can_delete_user_by_admin_with_permission(): void
    {
        $this->loginAsAdmin()->assignRole(
            $this->generateRole(
                'Admin can delete user',
                [
                    UserDeletePermission::KEY,
                ],
                Admin::GUARD
            )
        );

        $user = User::factory()->withCompany()->create();

        $result = $this->query($user->id)
            ->assertOk();

        $this->assertTrue($result->json('data.' . static::MUTATION));
        $this->assertDatabaseMissing(User::TABLE, ['id' => $user->id]);
    }
}
