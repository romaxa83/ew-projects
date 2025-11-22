<?php

namespace Tests\Feature\Http\Api\V1\Orders\BS\Action;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\BS\Order;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ReassignMechanicTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_update()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $old = clone $model->mechanic;

        /** @var $mechanic User */
        $mechanic = $this->userBuilder->asMechanic()->create();

        $data['mechanic_id'] = $mechanic->id;

        $this->assertNotEquals($model->mechanic_id, $mechanic->id);

        $this->postJson(route('api.v1.orders.bs.reassign-mechanic', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'mechanic' => [
                        'id' => $mechanic->id
                    ],
                ],
            ])
        ;

        $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.bs.reassigned_mechanic');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'mechanic_name' => $mechanic->full_name,
        ]);

        $this->assertEquals($history->details['mechanic'], [
            'old' => $old->full_name,
            'new' => $mechanic->full_name,
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function fail_user_not_mechanic()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $mechanic User */
        $mechanic = $this->userBuilder->asAdmin()->create();
        $data['mechanic_id'] = $mechanic->id;

        $res = $this->postJson(route('api.v1.orders.bs.reassign-mechanic', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res, __('validation.custom.user.role.mechanic_not_found'), 'mechanic_id');
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.orders.bs.reassign-mechanic', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['mechanic_id', null, 'validation.required', ['attribute' => 'validation.attributes.mechanic_id']],
            ['mechanic_id', 99999, 'validation.exists', ['attribute' => 'validation.attributes.mechanic_id']],
        ];
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $mechanic User */
        $mechanic = $this->userBuilder->asMechanic()->create();
        $data['mechanic_id'] = $mechanic->id;

        $res = $this->postJson(route('api.v1.orders.bs.reassign-mechanic', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $mechanic User */
        $mechanic = $this->userBuilder->asMechanic()->create();
        $data['mechanic_id'] = $mechanic->id;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.bs.reassign-mechanic', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $mechanic User */
        $mechanic = $this->userBuilder->asMechanic()->create();
        $data['mechanic_id'] = $mechanic->id;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.bs.reassign-mechanic', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
