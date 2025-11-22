<?php

namespace Feature\Http\Api\V1\Orders\BS\Action;

use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\TestCase;

class DeletePermanentlyTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.orders.bs.delete-permanently', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Order::query()->where('id', $id)->exists());
        $this->assertFalse(Order::query()->withTrashed()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-permanently', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-permanently', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-permanently', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
