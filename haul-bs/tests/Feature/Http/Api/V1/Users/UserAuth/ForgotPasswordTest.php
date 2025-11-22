<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAuth;

use App\Models\Users\User;
use App\Notifications\Auth\ForgotPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);

        $this->passportInit();
    }

    /** @test */
    public function success_forgot_password()
    {
        Notification::fake();

        /** @var $model User */
        $model = $this->userBuilder->create();

        $this->postJson(route('api.v1.users.forgot-password'), [
            'email' => $model->email->getValue()
        ])
            ->assertJson([
                'data' => [
                    'message' => __('messages.forgot_password.send.success', ['email' => $model->email->getValue()])
                ],
            ])
        ;

        $this->assertNotificationSentTo(
            $model->email->getValue(),
            ForgotPasswordNotification::class
        );
    }

    /** @test */
    public function fail_not_user()
    {
        Notification::fake();

        $wrongEmail = 'user@gmail.com';

        $res = $this->postJson(route('api.v1.users.forgot-password'), [
            'email' => $wrongEmail
        ])
        ;

        self::assertValidationMsg(
            $res,
            __('validation.exists', ['attribute' => __('validation.attributes.email')]),
            'email'
        );
    }
}
