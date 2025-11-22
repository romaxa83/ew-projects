<?php

namespace Tests\Feature\Mutations\BackOffice\Users;

use App\GraphQL\Mutations\BackOffice\Users\UserDeleteMutation;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Permissions\Users\UserDeletePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class UserDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = UserDeleteMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_cant_delete_user_by_other_user(): void
    {
        $this->loginAsUser();

        $user = User::factory()
            ->create();

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

        return $this->postGraphQLBackOffice(compact('query'));
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

        $user = User::factory()->create();

        $result = $this->query($user->id)
            ->assertOk();

        $this->assertEquals(true, $result->json('data.'.static::MUTATION));
        $this->assertDatabaseMissing(User::TABLE, ['id' => $user->id]);
    }
}
