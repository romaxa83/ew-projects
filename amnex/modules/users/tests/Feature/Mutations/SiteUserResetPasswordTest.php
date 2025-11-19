<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Services\Site\UserPasswordResetService;

class SiteUserResetPasswordTest extends TestCase
{
    private UserPasswordResetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(UserPasswordResetService::class);
    }

    /**
     * @throws Exception
     */
    public function testGuestCanResetPassword(): void
    {
        $oldPassword = 'password';
        $newPassword = 'new1password';

        $user = UserFactory::new()->create(['password_reset_code' => '123456']);

        self::assertTrue(Hash::check($oldPassword, $user->password));

        $token = $this->service->encryptToken($user);

        $response = $this->executeQuery([
            'token' => $token,
            'password' => $newPassword,
            'passwordConfirmation' => $newPassword,
        ])
            ->assertNoErrors();

        self::assertTrue($response->json('data.' . $this->operationName()));

        $user->refresh();

        self::assertTrue(Hash::check($newPassword, $user->password));
    }

    /**
     * @throws Exception
     */
    public function testInvalidToken(): void
    {
        $newPassword = 'new1password';

        $user = UserFactory::new()->create(['password_reset_code' => '123456']);

        $token = $this->service->encryptToken($user);

        $user->setPasswordResetCode('987654');
        $user->save();

        $this->executeQuery([
            'token' => $token,
            'password' => $newPassword,
            'passwordConfirmation' => $newPassword,
        ])
            ->assertHasErrorMessage(__('users::exceptions.invalid_password_reset_link'));
    }

    private function executeQuery(array $args = []): TestResponse
    {
        return $this->mutation()
            ->args($args)
            ->executeAndReturnResponse();
    }
}
