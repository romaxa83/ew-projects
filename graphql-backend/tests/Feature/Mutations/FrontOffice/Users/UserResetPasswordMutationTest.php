<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserResetPasswordMutation;
use App\Models\Users\User;
use App\Notifications\Users\UserResetPasswordVerification;
use App\Services\Users\UserVerificationService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserResetPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UserResetPasswordMutation::NAME;

    private User|Collection $user;
    private UserVerificationService $userVerificationService;

    /**
     * @throws Exception
     */
    public function test_user_reset_password(): void
    {
        Notification::fake();

        $this->query()
            ->assertOk();

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            UserResetPasswordVerification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === $this->user->getEmailString();
            }
        );
    }

    /**
     * @throws Exception
     */
    private function query(): TestResponse
    {
        return $this->postGraphQL(
            [
                'query' => sprintf(
                    'mutation { %s (token: "%s") }',
                    self::MUTATION,
                    $this->userVerificationService->encryptTokenForEmailReset($this->user),
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
