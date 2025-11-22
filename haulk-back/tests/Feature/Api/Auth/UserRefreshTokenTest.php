<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\LoginTrait;
use Tests\TestCase;

class UserRefreshTokenTest extends TestCase
{
    use DatabaseTransactions;
    use LoginTrait;

    public function test_it_refresh_token_success()
    {
        $data = $this->login();

        $this->postJson(route('auth.refresh-token'), ['refresh_token' => $data['refresh_token']])
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'token_type',
                        'expires_in',
                        'expires_at',
                        'access_token',
                        'refresh_token',
                    ]
                ]
            );
    }

    public function test_it_refresh_token_success_for_bs_users()
    {
        $data = $this->loginForBSUser();

        $this->postJson(route('auth.refresh-token'), ['refresh_token' => $data['refresh_token']])
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'token_type',
                        'expires_in',
                        'expires_at',
                        'access_token',
                        'refresh_token',
                    ]
                ]
            );
    }
}
