<?php

namespace Tests\Feature\Http\Api\V1\Users\UserCrud;

use App\Enums\Users\UserStatus;
use App\Foundations\Modules\Permission\Models\Role;
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
        $this->roleAdmin = Role::query()->select(['id'])->admin()->toBase()->first();

        $this->data = [
            'role_id' => $this->roleAdmin->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'jhon@doe.com',
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
        $this->loginUserAsSuperAdmin();

        /** @var $model User */
        $model = $this->userBuilder->asMechanic()->create();

        $data = $this->data;

        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->email, data_get($data, 'email'));
        $this->assertNotEquals($model->role->id, data_get($data, 'role_id'));
        $this->assertNotEquals($model->phone, data_get($data, 'phone'));
        $this->assertNotEquals($model->phones, data_get($data, 'phones'));
        $this->assertNotEquals($model->phone_extension, data_get($data, 'phone_extension'));
        $this->assertEquals($model->status, UserStatus::ACTIVE());

        $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'full_name' => data_get($data, 'first_name') .' '. data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => data_get($data, 'phone'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                    'status' => UserStatus::ACTIVE,
                    'deleted_at' => null,
                    'role' => [
                        'id' => data_get($data, 'role_id'),
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_not_uniq_email()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model User */
        $model = $this->userBuilder->asMechanic()->create();

        $data = $this->data;
        $data['email'] = $model->email->getValue();

        $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model User */
        $model = $this->userBuilder->asMechanic()->create();

        $data['role_id'] = 0;

        $res = $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.exists', ['attribute' => __('validation.attributes.role_id')]),
            'role_id'
        );
    }

    /** @test */
    public function field_success_role_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model User */
        $model = $this->userBuilder->asMechanic()->create();

        $data['role_id'] = $this->roleAdmin->id;

        $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data, [
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
        $email = 'test@gmail.com';
        $user = $this->userBuilder->email($email)->create();
        $this->loginUserAsSuperAdmin($user);

        /** @var $model User */
        $model = $this->userBuilder->asMechanic()->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data)
        ;

        $attr = [];
        foreach ($attributes as $k => $v){
            $attr[$k] = __($v);
        }

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
            ['email', 'test', 'validation.email', ['attribute' => 'validation.attributes.email']],
            ['email', null, 'validation.required', ['attribute' => 'validation.attributes.email']],
            ['email', 'test@gmail.com', 'validation.unique', ['attribute' => 'validation.attributes.email']],
            ['phone', '8798-86976-', 'validation.custom.phone.phone_rule', ['attribute' => 'validation.attributes.phone']],
            ['phone', 11111, 'validation.string', ['attribute' => 'validation.attributes.phone']],
            ['phone_extension', 11111, 'validation.string', ['attribute' => 'validation.attributes.phone_extension']],
            ['role_id', null, 'validation.required', ['attribute' => 'validation.attributes.role_id']],
            ['role_id', 0, 'validation.exists', ['attribute' => 'validation.attributes.role_id']],
        ];
    }

    /** @test */
    public function not_perm_manipulate_for_this_role()
    {
        $this->loginUserAsAdmin();

        $data = $this->data;

        /** @var $model User */
        $model = $this->userBuilder->asSuperAdmin()->create();

        $res = $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model User */
        $model = $this->userBuilder->asMechanic()->create();

        $res = $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model User */
        $model = $this->userBuilder->asMechanic()->create();

        $res = $this->putJson(route('api.v1.users.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}

