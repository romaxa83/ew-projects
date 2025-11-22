<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserEmailVerificationMutation;
use App\Models\Users\User;
use App\Services\Users\UserVerificationService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserEmailVerificationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UserEmailVerificationMutation::NAME;

    private UserVerificationService $service;

    /**
     * @throws Exception
     */
    public function test_ot_send_email_verification_code_form_success(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->loginAsUser($user);

        $this->service->fillEmailVerificationCode($user);

        $query = sprintf(
            'mutation { %s ( code: "%s" )}',
            self::MUTATION,
            $user->getEmailVerificationCode()
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        [self::MUTATION => $data] = $result->json(['data']);

        self::assertTrue($data);
    }

    /**
     * @throws Exception
     */
    public function test_it_not_verify_email_by_incorrect_code(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->loginAsUser($user);

        $this->service->fillEmailVerificationCode($user);

        $query = sprintf(
            'mutation { %s ( code: "%s" )}',
            self::MUTATION,
            '001001'
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        [self::MUTATION => $data] = $result->json(['data']);

        self::assertFalse($data);
    }

    public function test_it_has_error_for_not_auth_user(): void
    {
        $query = sprintf(
            'mutation { %s ( code: "%s" )}',
            self::MUTATION,
            '001001'
        );

        $result = $this->postGraphQL(['query' => $query])
            ->assertOk();

        $errors = $result->json('errors');

        self::assertEquals('Unauthorized', array_shift($errors)['message']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();

        $this->service = $this->app->make(UserVerificationService::class);
    }
}
