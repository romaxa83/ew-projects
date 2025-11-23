<?php

namespace Tests\Feature\Modules\Users\V1\Auth;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\SmsVerifyBuilder;
use Tests\Builders\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class RefreshTokenTest extends TestCase
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
        // логинимся чтоб получить токены
        $res = $this->postJson(route('api.v1.mobile.user.login'), $data);

        $refreshToken = $res->json("data.refreshToken");

        $resRefresh = $this->postJson(route('api.v1.mobile.user.refresh-token'), [
            'refreshToken' => $refreshToken
        ])
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $this->assertNotEquals($refreshToken, $resRefresh->json("data.refreshToken"));
        $this->assertNotEquals($res->json("data.accessToken"), $resRefresh->json("data.accessToken"));
    }

    /** @test */
    public function fail_wrong_refresh_token(): void
    {
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $phone = '+380955545453';
        $this->userBuilder->setPhone($phone)->create();
        $data = [
            'phone' => $phone,
            'actionToken' => $token->action_token->getValue(),
        ];
        // логинимся чтоб получить токены
        $this->postJson(route('api.v1.mobile.user.login'), $data);

        $refreshToken = "wrong_refresh_token";

        $this->postJson(route('api.v1.mobile.user.refresh-token'), [
            'refreshToken' => $refreshToken
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse("The refresh token is invalid."));
    }

//    /** @test */
//    public function fail_refresh_token_expire(): void
//    {
//        $token = $this->smsVerifyBuilder->withActionToken()->create();
//        $phone = '+380955545453';
//        $this->userBuilder->setPhone($phone)->create();
//        $data = [
//            'phone' => $phone,
//            'actionToken' => $token->action_token->getValue(),
//        ];
//        // логинимся чтоб получить токены
//        $res = $this->postJson(route('api.v1.mobile.user.login'), $data);
//
//        $refreshToken = $res->json("data.refreshToken");
//
//        CarbonImmutable::setTestNow(Carbon::now()->addYear());
//
//        $resRefresh = $this->postJson(route('api.v1.mobile.user.refresh-token'), [
//            'refreshToken' => $refreshToken
//        ])->dump()
//            ->assertOk()
//            ->assertJsonStructure($this->structureTokens());
//
//        $this->assertNotEquals($refreshToken, $resRefresh->json("data.refreshToken"));
//        $this->assertNotEquals($res->json("data.accessToken"), $resRefresh->json("data.accessToken"));
//    }
}

