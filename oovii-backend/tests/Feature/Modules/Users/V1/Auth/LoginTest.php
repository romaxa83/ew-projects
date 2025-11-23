<?php

namespace Tests\Feature\Modules\Users\V1\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\SmsVerifyBuilder;
use Tests\Builders\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use Tests\Unit\users\Dto\UserDtoTest;

class LoginTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $userBuilder;
    private $smsVerifyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->smsVerifyBuilder = resolve(SmsVerifyBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $phone = '+380955545453';
        $this->userBuilder->setPhone($phone)->create();
        $data = [
            'phone' => $phone,
            'actionToken' => $token->action_token->getValue(),
        ];

        $this->postJson(
            route('api.v1.mobile.user.login'),
            $data
        )
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());
    }

    public function test_login_fails_with_nonactive_user(): void
    {
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $phone = '+380955545453';
        $this->userBuilder->setPhone($phone)->setActive(false)->create();
        $data = [
            'phone' => $phone,
            'actionToken' => $token->action_token->getValue(),
        ];

        $this->postJson(
            route('api.v1.mobile.user.login'),
            $data
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::admin.exception.Not found user by phone', [
                    'phone' => $phone
                ])
            ));
    }

    /** @test */
    public function fail_not_user(): void
    {
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $phone = '+380955545453';
        $data = [
            'phone' => $phone,
            'actionToken' => $token->action_token->getValue(),
        ];

        $this->postJson(
            route('api.v1.mobile.user.login'),
            $data
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::admin.exception.Not found user by phone', [
                    'phone' => $phone
                ])
            ));
    }

    /** @test */
    public function fail_without_action_token(): void
    {
        $phone = '+380955545453';
        $data = [
            'phone' => $phone,
        ];

        $this->postJson(
            route('api.v1.mobile.user.login'),
            $data
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __("cms-users::admin.validation.actionToken.required")
            ));
    }

    /** @test */
    public function update_data(): void
    {
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $data = UserDtoTest::data();
        $data['actionToken'] = $token->action_token->getValue();
        $user = $this->userBuilder->setPhone(array_get($data, 'phone'))->create();

        self::assertNull($user->fcm_token);
        self::assertNull($user->device_id);

        $this->postJson(
            route('api.v1.mobile.user.login'),
            $data
        )->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $user->refresh();
        self::assertEquals($user->fcm_token, array_get($data, 'fcmToken'));
        self::assertEquals($user->device_id, array_get($data, 'deviceId'));
    }
}

