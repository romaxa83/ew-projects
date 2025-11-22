<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Members\MemberResetPasswordMutation;
use App\Models\Users\User;
use App\Notifications\Members\MemberResetPasswordVerification;
use App\Services\Users\UserVerificationService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotifications;

class UserResetPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotifications;

    public const MUTATION = MemberResetPasswordMutation::NAME;

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

        $this->assertNotificationSentTo(
            $this->user->getEmailString(),
            MemberResetPasswordVerification::class
        );
    }

    /**
     * @throws Exception
     */
    private function query(): TestResponse
    {
        $password = 'Password123';

        return $this->postGraphQL(
            [
                'query' => sprintf(
                    'mutation { %s (token: "%s"  password: "%s" password_confirmation: "%s") }',
                    self::MUTATION,
                    $this->userVerificationService->encryptEmailToken($this->user),
                    $password,
                    $password
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
