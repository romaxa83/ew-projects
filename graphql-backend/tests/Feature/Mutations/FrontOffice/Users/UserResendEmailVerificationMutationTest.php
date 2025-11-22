<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserResendVerificationMutation;
use App\Models\Users\User;
use App\Notifications\Users\UserEmailVerification;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserResendEmailVerificationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UserResendVerificationMutation::NAME;

    /**
     * @throws Exception
     */
    public function test_it_resend_verification_code_success(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);

        $this->loginAsUser($user);

        $query = sprintf('mutation { %s}', self::MUTATION);
        $result = $this->postGraphQL(compact('query'))
            ->assertOk();

        [self::MUTATION => $data] = $result->json(['data']);

        self::assertTrue($data);

        Notification::assertSentTo(new AnonymousNotifiable(), UserEmailVerification::class);
    }

    public function test_it_not_resend_verification_code_for_already_verified_email(): void
    {
        $this->loginAsUser();

        $result = $this->mutation()
            ->assertOk();

        $this->assertServerError($result, __('exceptions.email_already_verified'));
    }

    protected function mutation(): TestResponse
    {
        $query = sprintf('mutation { %s }', self::MUTATION);

        return $this->postGraphQL(compact('query'));
    }

    public function test_it_has_error_for_not_auth_user(): void
    {
        $result = $this->mutation()
            ->assertOk();

        $errors = $result->json('errors');

        self::assertEquals('Unauthorized', array_shift($errors)['message']);
    }
}
