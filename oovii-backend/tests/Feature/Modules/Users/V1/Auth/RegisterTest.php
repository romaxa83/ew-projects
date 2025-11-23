<?php

namespace Tests\Feature\Modules\Users\V1\Auth;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\SmsVerifyBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use Tests\Unit\users\Dto\UserDtoTest;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class RegisterTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $smsVerifyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->smsVerifyBuilder = resolve(SmsVerifyBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $phone = '+380954514991';
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $data = UserDtoTest::data();
        $data['phone'] = $phone;
        $data['actionToken'] = $token->action_token->getValue();

        $this->post(route('api.v1.mobile.user.register'), $data)
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $user = User::query()->where('email', $data['email'])->first();

        self::assertEquals($user->phone, array_get($data, 'phone'));
        self::assertEquals($user->name, array_get($data, 'name'));
        self::assertEquals($user->surname, array_get($data, 'surname'));
        self::assertEquals($user->lang, array_get($data, 'lang'));
        self::assertEquals($user->fcm_token, array_get($data, 'fcmToken'));
        self::assertEquals($user->device_id, array_get($data, 'deviceId'));
        self::assertNotEquals($user->password, array_get($data, 'password'));
        self::assertNull($user->email_verified_at);
        self::assertTrue($user->phone_verified);
        self::assertTrue($user->active);
        self::assertEquals(UserStatus::DRAFT, $user->status);
        self::assertNull($user->inviter);

        $this->expectException(ModelNotFoundException::class);
        $token->refresh();
    }

    public function test_it_saves_user_inviter_on_register_user_with_ref_id(): void
    {
        $token = $this->smsVerifyBuilder->withActionToken()->create();

        /** @var User $inviter */
        $inviter = User::factory()->create();
        $data = UserDtoTest::data();
        $data['ref_id'] = $inviter->id;
        $data['actionToken'] = $token->action_token->getValue();

        unset(
            $data['password'],
            $data['lang'],
            $data['fcmToken'],
            $data['deviceId'],
        );

        $this->post(route('api.v1.mobile.user.register'), $data)
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $user = User::query()->where('email', $data['email'])->first();

        self::assertEquals($inviter->id, $user->inviter->id);
    }

    /** @test */
    public function success_only_required_fields(): void
    {
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $data = UserDtoTest::data();
        $data['actionToken'] = $token->action_token->getValue();

        unset(
            $data['password'],
            $data['lang'],
            $data['fcmToken'],
            $data['deviceId'],
        );

        $this->post(route('api.v1.mobile.user.register'), $data)
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $user = User::query()->where('email', $data['email'])->first();

        self::assertEquals($user->phone, array_get($data, 'phone'));
        self::assertEquals($user->name, array_get($data, 'name'));
        self::assertEquals($user->surname, array_get($data, 'surname'));
        self::assertNull($user->fcm_token);
        self::assertNull($user->device_id);
        self::assertEquals($user->lang, config('cms.core.translations.app.default'));
        self::assertNotEmpty($user->password);
        self::assertNull($user->email_verified_at);
        self::assertTrue($user->phone_verified);
        self::assertTrue($user->active);
        self::assertEquals($user->status, UserStatus::DRAFT);
    }

    /** @test */
    public function fail_without_action_token(): void
    {
        $phone = '+77051587227';
        $data = UserDtoTest::data();
        $data['phone'] = $phone;

        $this->postJson(route('api.v1.mobile.user.register'), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::site.validation.actionToken.required')
            ));
    }

    /** @test */
    public function success_phone_with_delimiter(): void
    {
        $phoneDelimiter = '+38(095)451-49-91';
        $phoneClear = '+380954514991';
        $data = UserDtoTest::data();
        $data['phone'] = $phoneDelimiter;
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $data['actionToken'] = $token->action_token->getValue();

        $this->post(route('api.v1.mobile.user.register'), $data)
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $user = User::query()->where('email', $data['email'])->first();

        self::assertEquals($user->phone, $phoneClear);
    }

    /** @test */
    public function success_phone_with_space(): void
    {
        $phoneDelimiter = '+38(095)451 49 91';
        $phoneClear = '+380954514991';
        $data = UserDtoTest::data();
        $data['phone'] = $phoneDelimiter;
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $data['actionToken'] = $token->action_token->getValue();

        $this->post(route('api.v1.mobile.user.register'), $data)
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $user = User::query()->where('email', $data['email'])->first();

        self::assertEquals($user->phone, $phoneClear);
    }

    /** @test */
    public function success_phone_kazah(): void
    {
        $phone = '+77051587227';
        $data = UserDtoTest::data();
        $data['phone'] = $phone;
        $token = $this->smsVerifyBuilder->withActionToken()->create();
        $data['actionToken'] = $token->action_token->getValue();

        $this->post(route('api.v1.mobile.user.register'), $data)
            ->assertOk()
            ->assertJsonStructure($this->structureTokens());

        $user = User::query()->where('email', $data['email'])->first();

        self::assertEquals($user->phone, $phone);
    }

    /** @test */
    public function fail_phone_wong_format(): void
    {
        $phone = '+180954514991';
        $data = UserDtoTest::data();
        $data['phone'] = $phone;

        $this->postJson(route('api.v1.mobile.user.register'), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-core::admin.validation.wrong_phone_format')
            ));
    }

    /** @test */
    public function fail_without_name(): void
    {
        $data = UserDtoTest::data();

        unset(
            $data['name']
        );

        $this->post(
            route('api.v1.mobile.user.register'),
            $data,
            $this->headers()
        )
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::site.validation.name.required')
            ))
        ;
    }

    /** @test */
    public function fail_without_surname(): void
    {
        $data = UserDtoTest::data();

        unset(
            $data['surname']
        );

        $this->postJson(route('api.v1.mobile.user.register'), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::site.validation.surname.required')
            ))
        ;
    }

    /** @test */
    public function fail_without_email(): void
    {
        $data = UserDtoTest::data();

        unset($data['email']);

        $this->postJson(route('api.v1.mobile.user.register'), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::site.validation.email.required')
            ));
    }

    /** @test */
    public function fail_not_unique_email(): void
    {
        $val = 'some@email.com';
        User::factory()->create([
            'email' => $val
        ]);

        $data = UserDtoTest::data();
        $data['email'] = $val;

        $this->postJson(route('api.v1.mobile.user.register'), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::site.validation.email.unique')
            ));
    }

    /** @test */
    public function fail_without_phone(): void
    {
        $data = UserDtoTest::data();

        unset($data['phone']);

        $this->postJson(route('api.v1.mobile.user.register'), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::site.validation.phone.required')
            ));
    }

    /** @test */
    public function fail_not_unique_phone(): void
    {
        $val = '+380954657898';
        User::factory()->create([
            'phone' => $val
        ]);

        $data = UserDtoTest::data();
        $data['phone'] = $val;

        $this->postJson(route('api.v1.mobile.user.register'), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::site.validation.phone.unique')
            ));
    }
}
