<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Users\User;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use Tests\Helpers\Traits\LoginTrait;
use Tests\TestCase;

class UserLogoutTest extends TestCase
{
    use DatabaseTransactions;
    use LoginTrait;

    /**
     * @throws Exception
     */
    public function test_it_logout_success()
    {
        $data = $this->login();

        $tokenTable = (new Token())->getTable();
        $this->assertDatabaseHas(
            $tokenTable,
            [
                'revoked' => 0,
                'user_id' => $this->authenticatedUser->id,
            ]
        );
        $refreshTokenTable = (new RefreshToken())->getTable();
        $this->assertDatabaseHas($refreshTokenTable, ['revoked' => 0]);

        $this->postJson(route('auth.logout'), [], ['Authorization' => sprintf('Bearer %s', $data['access_token'])])
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'message' => 'You have logged out.'
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            $tokenTable,
            [
                'revoked' => 0,
                'user_id' => $this->authenticatedUser->id,
            ]
        );
        $this->assertDatabaseMissing($refreshTokenTable, ['revoked' => 0]);
    }

    public function test_it_logout_success_for_bs_users()
    {
        $data = $this->loginForBSUser();

        $tokenTable = (new Token())->getTable();
        $this->assertDatabaseHas(
            $tokenTable,
            [
                'revoked' => 0,
                'user_id' => $this->authenticatedUser->id,
            ]
        );
        $refreshTokenTable = (new RefreshToken())->getTable();
        $this->assertDatabaseHas($refreshTokenTable, ['revoked' => 0]);

        $this->postJson(route('auth.logout'), [], ['Authorization' => sprintf('Bearer %s', $data['access_token'])])
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'message' => 'You have logged out.'
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            $tokenTable,
            [
                'revoked' => 0,
                'user_id' => $this->authenticatedUser->id,
            ]
        );
        $this->assertDatabaseMissing($refreshTokenTable, ['revoked' => 0]);
    }
}
