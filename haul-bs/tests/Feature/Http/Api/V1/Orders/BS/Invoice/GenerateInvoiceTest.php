<?php

namespace Feature\Http\Api\V1\Orders\BS\Invoice;

use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
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

        $this->getJson(route('api.v1.orders.bs.generate-invoice', ['id' => $model->id]))
            ->assertOk()
        ;

    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.bs.generate-invoice', ['id' => 99999999]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.bs.generate-invoice', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.bs.generate-invoice', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
