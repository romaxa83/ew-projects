<?php

namespace Tests\Unit\Events\Users;

use App\Events\Users\UserRegisteredEvent;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Notifications\Members\MemberEmailVerification;
use App\ValueObjects\Email;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserRegisteredEventTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws Exception
     */
    public function test_it_send_mail_to_confirm_email(): void
    {
        Notification::fake();

        $email = new Email('user@example.com');
        $attributes = [
            'email' => $email,
            'email_verification_code' => null,
            'email_verified_at' => null,
        ];

        $user = User::factory()->new($attributes)->create();

        $this->assertUsersHas(
            [
                'email' => $email,
                'email_verification_code' => null
            ]
        );

        Role::factory()->asDefault()->create();

        event(new UserRegisteredEvent($user));

        Notification::assertSentTo(new AnonymousNotifiable(), MemberEmailVerification::class);

        $this->assertUsersMissing(
            [
                'email' => $email,
                'email_verification_code' => null
            ]
        );
    }
}
