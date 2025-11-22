<?php

namespace Tests\Unit\Models\Users;

use App\Models\Users\User;
use App\Permissions\Users\UserCreatePermission;
use App\Permissions\Users\UserListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\PersonalAccessTokenResult;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class UserTokenTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public function test_it_login_user(): void
    {
        $this->passportInit();
        $user = User::factory()->create();

        $user->assignRole($this->generateRole('User role', [UserListPermission::KEY, UserCreatePermission::KEY]));

        $token = $user->createToken("Users");

        self::assertInstanceOf(PersonalAccessTokenResult::class, $token);

        self::assertTrue($user->can(UserListPermission::KEY));
        self::assertTrue($user->can(UserCreatePermission::KEY));
    }
}
