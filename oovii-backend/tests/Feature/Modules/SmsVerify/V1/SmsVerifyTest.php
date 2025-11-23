<?php

namespace Tests\Feature\Modules\SmsVerify\V1;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Config;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\SmsVerifyBuilder;
use Tests\Builders\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\SmsVerify\Models\SmsVerify;

class SmsVerifyTest extends TestCase
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
        Config::set('cms.sms-verify.config.sender.enable', false);
        $data = [
            'phone' => '+380955545453'
        ];

        $model = SmsVerify::query()->where('phone', $data['phone'])->first();
        self::assertNull($model);

        $res = $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )->assertOk()
            ->assertJsonStructure(
                $this->schemaResponse(
                    [
                        'smsToken',
                        'smsCode',
                    ]
                )
            );

        /** @var SmsVerify $model */
        $model = SmsVerify::query()->where('phone', $data['phone'])->first();
        self::assertNotNull($model);
        self::assertNotNull($model->code);
        self::assertNotNull($model->sms_token);
        self::assertNotNull($model->sms_token_expires);
        self::assertNull($model->action_token);
        self::assertNull($model->action_token_expires);

        self::assertEquals($res->json('data.smsToken'), $model->sms_token->getValue());
        self::assertEquals($res->json('data.smsCode'), $model->code);
    }

    /** @test */
    public function success_with_phone_without_code(): void
    {
        Config::set('cms.sms-verify.config.sender.enable', true);
        $data = [
            'phone' => '+380955545453'
        ];

        $res = $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )
            ->assertOk()
            ->assertJsonStructure(
                $this->schemaResponse(
                    [
                        'smsToken',
                        'smsCode',
                    ]
                )
            );

        /** @var SmsVerify $model */
        $model = SmsVerify::query()->where('phone', $data['phone'])->first();

        self::assertNotNull($model->code);

        self::assertEquals($res->json('data.smsToken'), $model->sms_token->getValue());
        self::assertNull($res->json('data.smsCode'));
    }

    /** @test */
    public function success_with_access_token(): void
    {
        Config::set('cms.sms-verify.config.sender.enable', false);
        $user = $this->userBuilder->create();
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        // логинимся чтоб получить accessToken
        $res = $this->postJson(
            route('api.v1.mobile.user.login'),
            [
                'phone' => $user->phone,
                'actionToken' => $token->action_token->getValue(),
            ]
        );

        $data = [
            'accessToken' => $res->json('data.accessToken')
        ];
        $res = $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )
            ->assertOk()
            ->assertJsonStructure(
                $this->schemaResponse(
                    [
                        'smsToken',
                        'smsCode',
                    ]
                )
            );

        /** @var SmsVerify $model */
        $model = SmsVerify::query()->where('phone', $user->phone)->first();
        self::assertNotNull($model);
        self::assertNotNull($model->code);
        self::assertNotNull($model->sms_token);
        self::assertNotNull($model->sms_token_expires);
        self::assertNull($model->action_token);
        self::assertNull($model->action_token_expires);

        self::assertEquals($res->json('data.smsToken'), $model->sms_token->getValue());
        self::assertEquals($res->json('data.smsCode'), $model->code);
    }

    /** @test */
    public function fail_not_valid_access_token(): void
    {
        $data = [
            'accessToken' => 'not_valid_token'
        ];

        $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(__('cms-users::admin.passport.exception.invalid access token')));
    }

    /** @test */
    public function fail_empty_body_query(): void
    {
        $data = [];

        $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(
                $this->structureErrorResponse(__('cms-sms-verify::admin.exception.phone or accessToken required'))
            );
    }

    /** @test */
    public function fail_response_as_exist_sms_token(): void
    {
        // при запросе на верификацию, есть запись и в ней активен smsToken
        $data = [
            'phone' => '+380957775544'
        ];
        $this->smsVerifyBuilder->setPhone($data["phone"])->create();

        $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(__('cms-sms-verify::site.exception.sms token active')));
    }

    /** @test */
    public function success_response_as_exist_sms_token_but_it_expires(): void
    {
        // при запросе на верификацию, есть запись но smsToken протух
        Config::set('cms.sms-verify.config.verify.sms_token_expired', 'PT5M');
        $data = [
            'phone' => '+380957775544'
        ];
        $model = $this->smsVerifyBuilder->setPhone($data["phone"])->create();

        CarbonImmutable::setTestNow(Carbon::now()->addMinutes(10));

        $res = $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )->assertOk()
            ->assertJsonStructure(
                $this->schemaResponse(
                    [
                        'smsToken',
                        'smsCode',
                    ]
                )
            );

        $this->expectException(ModelNotFoundException::class);
        $model->refresh();

        /** @var SmsVerify $newRecord */
        $newRecord = SmsVerify::query()->where('phone', $data["phone"])->first();
        self::assertNotEmpty($newRecord);
        self::assertEquals($newRecord->sms_token->getValue(), $res->json('data.smsToken'));
    }

    /** @test */
//    public function fail_response_as_exist_active_action_token(): void
//    {
//        // при запросе на верификацию, есть запись и в ней активен actionToken
//        $data = [
//            'phone' => '+380957775544'
//        ];
//        $this->smsVerifyBuilder->setPhone($data["phone"])->withActionToken()->create();
//
//        $this->postJson(
//            route('api.v1.mobile.sms-verify'),
//            $data
//        )->assertStatus(Response::HTTP_BAD_REQUEST)
//            ->assertJson($this->structureErrorResponse(__('cms-sms-verify::site.exception.action token active')));
//    }

    /** @test */
    public function success_response_as_exist_action_token_but_it_expires(): void
    {
        // при запросе на верификацию, есть запись но actionToken протух
        $data = [
            'phone' => '+380957775544'
        ];
        $model = $this->smsVerifyBuilder->setPhone($data["phone"])->withActionToken()->create();
        Config::set('cms.sms-verify.config.verify.sms_token_expired', 'PT1H');

        CarbonImmutable::setTestNow(Carbon::now()->addHours(2));

        $res = $this->postJson(
            route('api.v1.mobile.sms-verify'),
            $data
        )->assertOk()
            ->assertJsonStructure(
                $this->schemaResponse(
                    [
                        'smsToken',
                        'smsCode',
                    ]
                )
            );

        $this->expectException(ModelNotFoundException::class);
        $model->refresh();

        /** @var SmsVerify $newRecord */
        $newRecord = SmsVerify::query()->where('phone', $data["phone"])->first();
        self::assertNotEmpty($newRecord);
        self::assertEquals($newRecord->sms_token->getValue(), $res->json('data.smsToken'));
    }
}

