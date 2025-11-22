<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAuth;

use App\Enums\Users\UserStatus;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class LoginTest extends TestCase
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
    public function success_login()
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' => $password,
        ];

        $this->postJson(route('api.v1.users.login'), $data)
            ->assertJsonStructure([
                'data' => [
                    'token_type',
                    'expires_in',
                    'access_token',
                    'refresh_token',
                ]
            ])
        ;
    }

    /** @test */
    public function success_login_as_sales_manager()
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->asSalesManager()->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' => $password,
        ];

        $this->postJson(route('api.v1.users.login'), $data)
            ->assertJsonStructure([
                'data' => [
                    'token_type',
                    'expires_in',
                    'access_token',
                    'refresh_token',
                ]
            ])
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => null,
            'password' => $password,
        ];

        $res = $this->postJson(route('api.v1.users.login'), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email'
        );
    }

    /** @test */
    public function success_wrong_with_validate_only()
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' => $password,
        ];

        $this->postJson(route('api.v1.users.login'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_login_as_sales_manager_not_active()
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder
            ->password($password)
            ->status(UserStatus::INACTIVE())
            ->asSalesManager()
            ->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' => $password,
        ];

        $res = $this->postJson(route('api.v1.users.login'), $data)
        ;

        self::assertErrorMsg($res, __('exceptions.user.not_found'), Response::HTTP_UNAUTHORIZED);
    }

    /** @test  */
    public function fail_wrong_password(): void
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' =>  $password . '4',
        ];

        $res = $this->postJson(route('api.v1.users.login'), $data)
        ;

        self::assertErrorMsg($res, __('auth.invalid_credentials'), Response::HTTP_UNAUTHORIZED);
    }

    /** @test  */
    public function fail_wrong_email(): void
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => $model->email->getValue() . 'r',
            'password' => $password,
        ];

        $res = $this->postJson(route('api.v1.users.login'), $data)
        ;

        self::assertErrorMsg($res, __('auth.invalid_credentials'), Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $password = 'Password123';
        /** @var $model User */
        $model = $this->userBuilder->password($password)->create();

        $data = [
            'email' => $model->email->getValue(),
            'password' => $password,
        ];
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.users.login'), $data)
        ;

        $attr = [];
        foreach ($attributes as $k => $v){
            $attr[$k] = __($v);
        }

        $this->assertValidationMsg($res, __($msgKey, $attr), $field);
    }

    public static function validate(): array
    {
        return [
            ['email', 'test', 'validation.email', ['attribute' => 'validation.attributes.email']],
            ['email', null, 'validation.required', ['attribute' => 'validation.attributes.email']],
            ['password', null, 'validation.required', ['attribute' => 'validation.attributes.password']],
        ];
    }
}

