<?php

namespace Tests\Feature\Queries\BackOffice\Users;

use App\GraphQL\Queries\BackOffice\Users\UsersQueryForAdminPanel;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use App\Permissions\Users\UserCreatePermission;
use App\Permissions\Users\UserDeletePermission;
use App\Permissions\Users\UserListPermission;
use App\Permissions\Users\UserUpdatePermission;
use App\ValueObjects\Email;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class UsersQueryForAdminPanelTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = UsersQueryForAdminPanel::NAME;

    public function test_cant_get_list_of_users_for_simple_user(): void
    {
        $this->loginAsUser();

        $this->test_cant_get_list_of_users();
    }

    public function test_cant_get_list_of_users(): void
    {
        $query = sprintf(
            'query { %s { data { first_name } } }',
            self::QUERY
        );
        $result = $this->postGraphQLBackOffice(compact('query'));
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_get_list_of_users_for_admin_without_permission(): void
    {
        $this->loginAsAdmin();

        $this->test_cant_get_list_of_users();
    }

    public function test_admin_with_correct_permission_can_see_a_list_of_users(): void
    {
        $this->loginAsUserManagerAdmin();

        User::factory()->times(30)->create();

        $query = sprintf(
            'query { %s (per_page: %s) { data { first_name email } } }',
            self::QUERY,
            50
        );
        $result = $this->postGraphQLBackOffice(compact('query'));

        $users = $result->json('data.'.self::QUERY.'.data');

        self::assertCount(30, $users);
    }

    protected function loginAsUserManagerAdmin(): Admin
    {
        $admin = $this->loginAsAdmin();
        $admin->assignRole(
            $this->generateRole(
                'UserManager',
                [
                    UserListPermission::KEY,
                    UserCreatePermission::KEY,
                    UserUpdatePermission::KEY,
                    UserDeletePermission::KEY
                ],
                Admin::GUARD
            )
        );

        return $admin;
    }

    public function test_admin_can_find_user_by_email_chunk(): void
    {
        $this->loginAsUserManagerAdmin();
        $searchEmail = 'fake.email@example.com';
        User::factory()->create(['email' => new Email($searchEmail)]);

        User::factory()->times(30)->create();

        $searchString = 'fake.email';

        $query = sprintf(
            'query { %s (query: "%s" ) { data { first_name email } } }',
            self::QUERY,
            $searchString
        );

        $result = $this->postGraphQLBackOffice(compact('query'));

        $users = $result->json('data.'.self::QUERY.'.data');
        self::assertCount(1, $users);

        $user = array_shift($users);
        self::assertEquals($searchEmail, $user['email']);
    }
}
