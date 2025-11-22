<?php

namespace Tests\Unit\Models\Users;

use App\Models\Users\User;
use App\Permissions\Users\UserCreatePermission;
use App\Permissions\Users\UserShowPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\PersonalAccessTokenResult;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class UserTokenTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public function test_it_login_user(): void
    {
        $this->passportInit();
        $user = User::factory()
            ->create();

        $user->assignRole($this->generateRole('User role', [UserShowPermission::KEY, UserCreatePermission::KEY]));

        $token = $user->createToken("Users");

        self::assertInstanceOf(PersonalAccessTokenResult::class, $token);

        self::assertTrue($user->can(UserShowPermission::KEY));
        self::assertTrue($user->can(UserCreatePermission::KEY));
    }
}
