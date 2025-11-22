<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAuth;

use App\Models\Users\User;
use App\Services\Users\VerificationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CheckPasswordTokenTest extends TestCase
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
    public function success_check()
    {
        /** @var $model User */
        $model = $this->userBuilder->create();

        $token = $this->verificationService->getTokenForPassword($model);

        $this->postJson(route('api.v1.users.check-password-token'), [
            'token' => $token
        ])
            ->assertJson([
                'data' =>  [
                    'message' => __('messages.token.valid')
                ],
            ])
        ;
    }

    /** @test */
    public function fail_check_expired()
    {
        /** @var $model User */
        $model = $this->userBuilder->create();

        $token = $this->verificationService->getTokenForPassword($model);

        $date = CarbonImmutable::now()->addDays();
        CarbonImmutable::setTestNow($date);

        $res = $this->postJson(route('api.v1.users.check-password-token'), [
            'token' => $token
        ])
        ;

        self::assertErrorMsg($res, __('messages.token.not_valid'));
    }

    /** @test */
    public function fail_check_not_model()
    {
        /** @var $model User */
        $model = $this->userBuilder->create();

        $token = $this->verificationService->getTokenForPassword($model);

        $model->delete();

        $res = $this->postJson(route('api.v1.users.check-password-token'), [
            'token' => $token
        ])
        ;

        self::assertErrorMsg($res, __('messages.token.not_valid'));
    }

    /** @test */
    public function fail_check_not_check_code()
    {
        /** @var $model User */
        $model = $this->userBuilder->create();

        $token = $this->verificationService->getTokenForPassword($model);

        $model->password_verified_code = $model->password_verified_code . '33';
        $model->save();

        $res = $this->postJson(route('api.v1.users.check-password-token'), [
            'token' => $token
        ])
        ;

        self::assertErrorMsg($res, __('messages.token.not_valid'));
    }
}
