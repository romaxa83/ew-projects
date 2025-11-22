<?php

namespace Tests\Feature\Http\Api\V1\Users\UserAction;

use App\Enums\Users\UserStatus;
use App\Events\Events\Users\UserChangedEvent;
use App\Events\Listeners\Users\SendNotificationChangePasswordListener;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    protected $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);

        $this->data = [
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
    }

    /** @test */
    public function success_change()
    {
        Event::fake([UserChangedEvent::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        /** @var $model User */
        $model = $this->userBuilder->asAdmin()->create();

        $this->assertFalse(password_verify($data['password'], $model->password));

        $this->putJson(route('api.v1.users.change-password', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'message' => __('messages.user.change_password'),
                ]
            ])
        ;

        $model->refresh();

        $this->assertTrue(password_verify($data['password'], $model->password));

        Event::assertDispatched(fn (UserChangedEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(UserChangedEvent::class, SendNotificationChangePasswordListener::class);
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

        /** @var $model User */
        $model = $this->userBuilder->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.change-password', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['password', null, 'validation.required', ['attribute' => 'validation.attributes.password']],
            ['password', '12', 'validation.min.string', ['attribute' => 'validation.attributes.password', 'min' => User::MIN_LENGTH_PASSWORD]],
            ['password', 'aaaaaaaaaaaaa', 'validation.custom.password.password_rule', ['attribute' => 'validation.attributes.password']],
            ['password', '1111111111111', 'validation.custom.password.password_rule', ['attribute' => 'validation.attributes.password']],
            ['password_confirmation', null, 'validation.required', ['attribute' => 'password confirmation']],
            ['password_confirmation', 'password12', 'validation.same', ['attribute' => 'password confirmation', 'other' => 'validation.attributes.password']],
        ];
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->putJson(route('api.v1.users.change-password', ['id' => 0]), $data);

        self::assertErrorMsg($res, __("exceptions.user.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm_manipulate_for_this_role()
    {
        $this->loginUserAsAdmin();

        $data = $this->data;

        /** @var $model User */
        $model = $this->userBuilder->asSuperAdmin()->create();

        $res = $this->putJson(route('api.v1.users.change-password', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.change-password', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model User */
        $model = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();

        $res = $this->putJson(route('api.v1.users.change-password', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
