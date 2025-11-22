<?php

namespace Feature\Http\Api\V1\Orders\BS\Crud;

use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected UserBuilder $userBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        $truck = $this->truckBuilder->create();
        $user = $this->userBuilder->asMechanic()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->vehicle($truck)->mechanic($user)->create();

        $this->getJson(route('api.v1.orders.bs.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                    'vehicle' => [
                        'id' => $truck->id,
                        'vin' => $truck->vin,
                        'unit_number' => $truck->unit_number,
                    ],
                    'discount' => $model->discount,
                    'tax_labor' => $model->tax_labor,
                    'tax_inventory' => $model->tax_inventory,
                    'implementation_date' => to_bs_timezone($model->implementation_date)->format('Y-m-d H:i'),
                    'due_date' => $model->due_date->format('Y-m-d'),
                    'notes' => $model->notes,
                    'mechanic' => [
                        'id' => $user->id
                    ],
                    'customer' => [
                        'id' => $truck->customer->id
                    ],
                    'status' => $model->status->value,
                    'payment_status' => $model->getCurrentPaymentStatus(),
                    'total_amount' => $model->total_amount,
                    'is_prices_changed' => $model->isPricesChanged(),
                    'billed_at' => $model->billed_at?->timestamp,
                    'paid_at' => $model->paid_at?->timestamp,
                    'status_changed_at' => $model->status_changed_at?->timestamp,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_if_deleted_vehicle()
    {
        $this->loginUserAsSuperAdmin();

        $truck = $this->truckBuilder->delete()->create();
        $user = $this->userBuilder->asMechanic()->create();

        /** @var $model Order */
        $model = $this->orderBuilder->vehicle($truck)->mechanic($user)->create();

        $this->getJson(route('api.v1.orders.bs.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'vehicle' => [
                        'id' => $truck->id,
                    ],
                    'customer' => [
                        'id' => $truck->customer->id
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.orders.bs.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.bs.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.bs.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
