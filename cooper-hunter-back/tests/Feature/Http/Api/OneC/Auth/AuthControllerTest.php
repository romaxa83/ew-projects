<?php

namespace Tests\Feature\Http\Api\OneC\Auth;

use App\Models\OneC\Moderator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_success(): void
    {
        $moderator = Moderator::factory()->create();

        $data = [
            'email' => (string)$moderator->email,
            'password' => 'password',
        ];

        $this->postJson(
            route('1c.auth.login'),
            $data
        )->assertOk()
            ->assertJsonStructure(
                $this->assertLoggedInStructure()
            );
    }

    protected function assertLoggedInStructure(): array
    {
        return [
            'data' => [
                'token_type',
                'access_expires_in',
                'refresh_expires_in',
                'access_token',
                'refresh_token',
            ],
        ];
    }

    public function test_login_invalid_credentials(): void
    {
        $moderator = Moderator::factory()->create();

        $data = [
            'email' => (string)$moderator->email,
            'password' => 'passwordWrong',
        ];

        $this->postJson(
            route('1c.auth.login'),
            $data
        )->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function test_logged_in_login(): void
    {
        $moderator = $this->loginAsModerator();

        $data = [
            'email' => (string)$moderator->email,
            'password' => 'password',
        ];

        $this->postJson(
            route('1c.auth.login'),
            $data
        )
            ->assertForbidden();
    }

    public function test_refresh_token(): void
    {
        $moderator = Moderator::factory()->create();

        $data = [
            'email' => (string)$moderator->email,
            'password' => 'password',
        ];

        $token = $this->postJson(
            route('1c.auth.login'),
            $data
        )->assertOk()->json('data.refresh_token');

        $this->postJson(
            route('1c.auth.refresh', $token),
        )->assertOk()
            ->assertJsonStructure(
                $this->assertLoggedInStructure()
            );
    }

    public function test_logout(): void
    {
        $moderator = Moderator::factory()->create();

        $data = [
            'email' => (string)$moderator->email,
            'password' => 'password',
        ];

        $token = $this->postJson(
            route('1c.auth.login'),
            $data
        )->assertOk()->json('data.access_token');

        $header = ['Authorization' => 'Bearer ' . $token];

        $this->postJson(
            route('1c.auth.logout'),
            headers: $header
        )->assertOk()
            ->assertJsonPath('data', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }
}
