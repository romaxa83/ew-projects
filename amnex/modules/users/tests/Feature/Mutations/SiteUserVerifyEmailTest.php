<?php

namespace Wezom\Users\Tests\Feature\Mutations;

use Exception;
use Illuminate\Testing\TestResponse;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Services\Site\UserVerificationService;
use Wezom\Users\Traits\UserTestTrait;

class SiteUserVerifyEmailTest extends TestCase
{
    use UserTestTrait;

    private UserVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(UserVerificationService::class);
    }

    /**
     * @throws Exception
     */
    public function testGuestCanVerifyEmail(): void
    {
        $user = UserFactory::new()->unverified()->create();

        $this->service->setEmailVerificationCode($user);

        $token = $this->service->encryptToken($user);

        $response = $this->executeQuery(compact('token'));

        $response->assertJson(['data' => [$this->operationName() => true]]);

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    /**
     * @throws Exception
     */
    public function testInvalidToken(): void
    {
        $user = UserFactory::new()->unverified()->create();

        $this->loginAsUser($user);

        $this->service->setEmailVerificationCode($user);

        $token = $this->service->encryptToken($user);

        $this->service->setEmailVerificationCode($user);

        $response = $this->executeQuery(compact('token'));

        $this->assertGraphQlInternal($response, __('users::exceptions.invalid_verification_link'));
    }

    /**
     * @throws Exception
     */
    public function testAlreadyVerifiedEmail(): void
    {
        $user = UserFactory::new()->create(['email_verified_at' => now()]);

        $this->loginAsUser($user);

        $this->service->setEmailVerificationCode($user);

        $token = $this->service->encryptToken($user);

        $response = $this->executeQuery(compact('token'));

        $this->assertGraphQlInternal($response, __('users::exceptions.email_already_verified'));
    }

    private function executeQuery(array $args = []): TestResponse
    {
        return $this->mutation()
            ->args($args)
            ->executeAndReturnResponse();
    }
}
