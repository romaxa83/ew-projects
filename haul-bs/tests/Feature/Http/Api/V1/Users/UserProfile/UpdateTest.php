<?php

namespace Tests\Feature\Http\Api\V1\Users\UserProfile;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected $roleAdmin;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);

        $this->data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '14444444449',
            'phone_extension' => '449',
            'phones' => [
                [
                    "number" => "15555555555",
                    "extension" => "4111"
                ],
                [
                    "number" => "14444444444",
                    "extension" => "5111"
                ]
            ]
        ];
    }

    /** @test */
    public function success_update()
    {
        /** @var $model User */
        $model = $this->userBuilder->asSuperAdmin()
            ->phone('17777777777')
            ->phoneExt('777')
            ->create();

        $this->loginUserAsSuperAdmin($model);

        $data = $this->data;

        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->phone, data_get($data, 'phone'));
        $this->assertNotEquals($model->phones, data_get($data, 'phones'));
        $this->assertNotEquals($model->phone_extension, data_get($data, 'phone_extension'));

        $this->putJson(route('api.v1.users.profile.update'), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'full_name' => data_get($data, 'first_name') .' '. data_get($data, 'last_name'),
                    'phone' => data_get($data, 'phone'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_only_required()
    {
        /** @var $model User */
        $model = $this->userBuilder->asSuperAdmin()
            ->phone('17777777777')
            ->phoneExt('777')
            ->create();

        $this->loginUserAsSuperAdmin($model);

        $data = $this->data;
        unset(
            $data['phone'],
            $data['phones'],
            $data['phone_extension'],
        );

        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'first_name'));

        $this->putJson(route('api.v1.users.profile.update'), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'full_name' => data_get($data, 'first_name') .' '. data_get($data, 'last_name'),
                    'phone' => $model->phone,
                    'phone_extension' => $model->phone_extension,
                    'phones' => $model->phones,
                ],
            ])
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['first_name'] = '1111';

        $res = $this->putJson(route('api.v1.users.profile.update'), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.alpha_spaces', ['attribute' => __('validation.attributes.first_name')]),
            'first_name'
        );
    }

    /** @test */
    public function field_success_role_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['first_name'] = 'Allen';

        $this->putJson(route('api.v1.users.profile.update'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->putJson(route('api.v1.users.profile.update'), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['first_name', null, 'validation.required', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', '1212', 'validation.alpha_spaces', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', 111, 'validation.string', ['attribute' => 'validation.attributes.first_name']],
            ['last_name', null, 'validation.required', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', '1212', 'validation.alpha_spaces', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', 111, 'validation.string', ['attribute' => 'validation.attributes.last_name']],
        ];
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->putJson(route('api.v1.users.profile.update'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
