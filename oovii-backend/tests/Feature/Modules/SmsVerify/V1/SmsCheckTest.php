<?php

namespace Tests\Feature\Modules\SmsVerify\V1;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Config;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\SmsVerifyBuilder;
use Tests\Builders\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class SmsCheckTest extends TestCase
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
    public function success_with_phone(): void
    {
        $phone = "+380957775544";
        $model = $this->smsVerifyBuilder->setPhone($phone)->create();
        $model->refresh();

        $this->assertNull($model->action_token);

        $data = [
            "smsToken" => $model->sms_token->getValue(),
            "code" => $model->code,
        ];

        $res = $this->postJson(
            route('api.v1.mobile.sms-check'),
            $data
        )
            ->assertOk()
            ->assertJsonStructure($this->schemaResponse([
                "actionToken"
            ]));

        $model->refresh();

        self::assertNotNull($model->action_token);
        self::assertEquals($res->json('data.actionToken'), $model->action_token->getValue());
    }

    /** @test */
    public function fail_expire_sms_token(): void
    {
        Config::set('cms.sms-verify.config.verify.sms_token_expired', 'PT5M');
        $phone = "+380957775544";
        $model = $this->smsVerifyBuilder->setPhone($phone)->create();

        CarbonImmutable::setTestNow(Carbon::now()->addMinutes(10));

        $data = [
            "smsToken" => $model->sms_token->getValue(),
            "code" => $model->code,
        ];

        $this->postJson(
            route('api.v1.mobile.sms-check'),
            $data
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(__("cms-sms-verify::site.exception.expired sms token")));
    }

    /** @test */
    public function fail_wrong_sms_code(): void
    {
        $phone = "+380957775544";
        $model = $this->smsVerifyBuilder->setPhone($phone)->create();

        $data = [
            "smsToken" => $model->sms_token->getValue(),
            "code" => "0000",
        ];

        $this->postJson(
            route('api.v1.mobile.sms-check'),
            $data
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(__("cms-sms-verify::site.exception.not equals sms code")));
    }
}

