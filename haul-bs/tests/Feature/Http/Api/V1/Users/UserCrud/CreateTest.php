<?php

namespace Tests\Feature\Http\Api\V1\Users\UserCrud;

use App\Enums\Users\UserStatus;
use App\Foundations\Modules\Permission\Models\Role;
use App\Models\Users\User;
use App\Notifications\Auth\ConfirmRegistrationNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
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
    public function success_create()
    {
        Notification::fake();

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $id = $this->postJson(route('api.v1.users.store'), $data)
            ->assertJson([
                'data' => [
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'full_name' => data_get($data, 'first_name') .' '. data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => data_get($data, 'phone'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                    'status' => UserStatus::PENDING,
                    'deleted_at' => null,
                    'role' => [
                        'id' => data_get($data, 'role_id'),
                    ]
                ],
            ])
            ->json('data.id')
        ;

        /** @var $model User */
        $model = User::find($id);

        $this->assertNull($model->password);
        $this->assertNull($model->email_verified_at);
        $this->assertTrue($model->role->isAdmin());

        $this->assertNotificationSentTo(
            $model->email->getValue(),
            ConfirmRegistrationNotification::class
        );
    }

    /** @test */
    public function success_create_as_sales_manager()
    {
        Notification::fake();

        $this->loginUserAsAdmin();

        $role = Role::salesManager()->first();

        $data = $this->data;
        $data['role_id'] = $role->id;

        $id = $this->postJson(route('api.v1.users.store'), $data)
            ->assertJson([
                'data' => [
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'full_name' => data_get($data, 'first_name') .' '. data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => data_get($data, 'phone'),
                    'phone_extension' => data_get($data, 'phone_extension'),
                    'phones' => data_get($data, 'phones'),
                    'status' => UserStatus::PENDING,
                    'deleted_at' => null,
                    'role' => [
                        'id' => data_get($data, 'role_id'),
                    ]
                ],
            ])
            ->json('data.id')
        ;

        /** @var $model User */
        $model = User::find($id);

        $this->assertNull($model->password);
        $this->assertNull($model->email_verified_at);
        $this->assertTrue($model->role->isSalesManager());

        $this->assertNotificationSentTo(
            $model->email->getValue(),
            ConfirmRegistrationNotification::class
        );
    }

    /** @test */
    public function success_create_not_send_confirm_as_mechanic()
    {
        Notification::fake();

        $this->loginUserAsSuperAdmin();

        $role = Role::query()->select(['id'])->mechanic()->toBase()->first();

        $data = $this->data;
        $data['role_id'] = $role->id;

        $id = $this->postJson(route('api.v1.users.store'), $data)
            ->assertJson([
                'data' => [
                    'role' => [
                        'id' => data_get($data, 'role_id'),
                    ]
                ],
            ])
            ->json('data.id')
        ;

        /** @var $model User */
        $model = User::find($id);

        $this->assertTrue($model->role->isMechanic());

        $this->assertNotificationNotSentTo(
            $model->email->getValue(),
            ConfirmRegistrationNotification::class
        );
    }

    /** @test */
    public function success_create_only_required_data()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        unset(
            $data['phone'],
            $data['phone_extension'],
            $data['phones'],
        );

        $this->postJson(route('api.v1.users.store'), $data)
            ->assertJson([
                'data' => [
                    'phone' => null,
                    'phone_extension' => null,
                    'phones' => null,
                    'status' => UserStatus::PENDING,
                    'deleted_at' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['role_id'] = 0;

        $res = $this->postJson(route('api.v1.users.store'), $data, [
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

        $data['role_id'] = $this->roleAdmin->id;

        $this->postJson(route('api.v1.users.store'), $data, [
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

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.users.store'), $data)
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

        $role = Role::query()->select(['id'])->superAdmin()->toBase()->first();

        $data = $this->data;
        $data['role_id'] = $role->id;

        $res = $this->postJson(route('api.v1.users.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.users.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.users.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
