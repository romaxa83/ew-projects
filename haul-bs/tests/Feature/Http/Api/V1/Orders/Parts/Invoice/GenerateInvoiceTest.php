<?php

namespace Feature\Http\Api\V1\Orders\Parts\Invoice;

use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class GenerateInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_generate()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.parts.generate-invoice', ['id' => $model->id]))
            ->assertOk()
        ;

    }

    /** @test */
    public function fail_order_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $res = $this->getJson(route('api.v1.orders.parts.generate-invoice', ['id' => $model->id]))
        ;

        self::assertErrorMsg(
            $res,
            __("exceptions.orders.parts.must_not_be_draft"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.generate-invoice', ['id' => 99999999]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.generate-invoice', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.generate-invoice', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
