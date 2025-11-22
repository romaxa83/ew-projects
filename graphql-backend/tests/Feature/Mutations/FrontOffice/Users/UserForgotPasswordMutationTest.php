<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserForgotPasswordMutation;
use App\Models\Users\User;
use App\Notifications\Users\UserForgotPasswordVerification;
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

    public const MUTATION = UserForgotPasswordMutation::NAME;

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
            UserForgotPasswordVerification::class,
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
                    'mutation { %s (email: "%s", link: "%s") }',
                    self::MUTATION,
                    $this->user->email,
                    'http://localhost/',
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
