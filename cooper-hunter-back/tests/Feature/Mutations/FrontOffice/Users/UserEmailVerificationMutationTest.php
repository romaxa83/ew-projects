<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Members\MemberEmailConfirmationMutation;
use App\Models\Users\User;
use App\Services\Users\UserVerificationService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserEmailVerificationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberEmailConfirmationMutation::NAME;

    private UserVerificationService $service;

    /**
     * @throws Exception
     */
    public function test_it_verify_email_success(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $token = $this->service->encryptEmailToken($user);

        $query = sprintf(
            'mutation { %s ( token: "%s" )}',
            self::MUTATION,
            $token
        );

        $result = $this->postGraphQL(compact('query'))
            ->assertOk();

        [self::MUTATION => $data] = $result->json(['data']);

        self::assertTrue($data);

        $user->refresh();
    }

    /**
     * @throws Exception
     */
    public function test_it_not_verify_email_by_incorrect_code(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->service->encryptEmailToken($user);

        $wrongToken = $this->service->encryptEmailToken(User::factory()->notVerified()->create());

        $query = sprintf(
            'mutation { %s ( token: "%s" )}',
            self::MUTATION,
            $wrongToken
        );

        $this->postGraphQL(compact('query'))
            ->assertOk();

        $this->assertDatabaseHas(
            User::TABLE,
            [
                'email_verified_at' => null
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();

        $this->service = $this->app->make(UserVerificationService::class);
    }
}
