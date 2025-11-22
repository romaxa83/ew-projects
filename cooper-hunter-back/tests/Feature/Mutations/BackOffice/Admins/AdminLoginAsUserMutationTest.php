<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminLoginAsUserMutation;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Permissions\Admins\AdminLoginAsUserPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class AdminLoginAsUserMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = AdminLoginAsUserMutation::NAME;

    protected ?User $user;

    public function test_user_cant_use_login_as_another_user_feature(): void
    {
        $this->loginAsUser();

        $this->test_not_auth_user_cant_use_login_as_user_feature();
    }

    public function test_not_auth_user_cant_use_login_as_user_feature(): void
    {
        $result = $this->query()
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    protected function query(): TestResponse
    {
        $query = sprintf(
            'mutation { %s ( user_id: %d ) { access_token expires_in token_type } }',
            self::MUTATION,
            $this->user->id
        );

        return $this->postGraphQLBackOffice(compact('query'));
    }

    public function test_admin_without_permissions_cant_use_login_as_another_user_feature(): void
    {
        $this->loginAsAdmin();

        $this->test_not_auth_user_cant_use_login_as_user_feature();
    }

    public function test_admin_with_correct_permission_can_get_user_access_token(): void
    {
        $this->loginAsUserManager();

        $result = $this->query();
        $data = $result->json('data.'.self::MUTATION);

        self::assertEquals('Bearer', $data['token_type']);

        $this->assertDatabaseHas(
            'oauth_access_tokens',
            [
                'user_id' => $this->user->id,
                'name' => User::GUARD
            ]
        );

        self::assertArrayHasKey('access_token', $data);
        self::assertArrayHasKey('expires_in', $data);
    }

    protected function loginAsUserManager(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole(
                    'Almighty admin',
                    [
                        AdminLoginAsUserPermission::KEY
                    ],
                    Admin::GUARD
                )
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
        $this->user = User::factory()
            ->create();
    }
}
