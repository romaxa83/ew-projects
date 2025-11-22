<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Members\MemberForgotPasswordMutation;
use App\Models\Users\User;
use App\Notifications\Members\MemberForgotPasswordVerification;
use App\Services\Users\UserVerificationService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserForgotPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberForgotPasswordMutation::NAME;

    private User|Collection $user;
    private UserVerificationService $userVerificationService;

    /**
     * @throws Exception
     */
    public function test_user_forgot_password(): void
    {
        Notification::fake();

        $this->query()
            ->assertOk();

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            MemberForgotPasswordVerification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === $this->user->getEmailString();
            }
        );
    }

    private function query(): TestResponse
    {
        return $this->postGraphQL(
            [
                'query' => sprintf(
                    'mutation { %s (email: "%s") }',
                    self::MUTATION,
                    $this->user->email,
                )
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->userVerificationService = app(UserVerificationService::class);
    }
}
