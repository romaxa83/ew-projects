<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAuth;

use App\Models\Users\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Services\Users\VerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected VerificationService $verificationService;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
        $this->verificationService = resolve(VerificationService::class);

        $this->passportInit();
    }

    /** @test */
    public function success_reset_password()
    {
        Notification::fake();

        /** @var $model User */
        $model = $this->userBuilder->notVerifyEmail()->create();

        $password = 'Password1234';

        $this->assertFalse(Hash::check($password, $model->password));
        $this->assertNull($model->email_verified_at);

        $this->postJson(route('api.v1.users.reset-password'), [
            'token' => $this->verificationService->getTokenForPassword($model),
            'password' => $password,
            'password_confirmation' => $password,
        ])
            ->assertJson([
                'data' => [
                    'message' => __('messages.reset_password.success')
                ],
            ])
        ;

        $this->assertNotificationSentTo(
            $model->email->getValue(),
            ResetPasswordNotification::class
        );

        $model->refresh();

        $this->assertTrue(Hash::check($password, $model->password));
        $this->assertNotNull($model->email_verified_at);
    }

    /** @test */
    public function fail_wrong_token()
    {
        Notification::fake();

        /** @var $model User */
        $model = $this->userBuilder->create();

        $password = 'Password1234';

        $this->assertFalse(Hash::check($password, $model->password));

        $res = $this->postJson(route('api.v1.users.reset-password'), [
            'token' => 'wrong_token',
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ;

        $this->assertErrorMsg($res, "The payload is invalid.");

        $this->assertNotificationNotSentTo(
            $model->email->getValue(),
            ResetPasswordNotification::class
        );
    }

    /** @test */
    public function fail_wrong_code()
    {
        Notification::fake();

        /** @var $model User */
        $model = $this->userBuilder->create();

        $password = 'Password1234';

        $token = $this->verificationService->getTokenForPassword($model);

        $model->password_verified_code = $model->password_verified_code . '66';
        $model->save();

        $res = $this->postJson(route('api.v1.users.reset-password'), [
            'token' => $token,
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ;

        $this->assertErrorMsg($res, "Token not verified");

        $this->assertNotificationNotSentTo(
            $model->email->getValue(),
            ResetPasswordNotification::class
        );
    }
}
