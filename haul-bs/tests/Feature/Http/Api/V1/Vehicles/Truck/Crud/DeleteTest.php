<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Truck\Crud;

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected TruckBuilder $truckBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder
            ->vehicle($model)
            ->status(OrderStatus::Finished->value, CarbonImmutable::now()->subDays(3))
            ->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Truck::query()->where('id', $id)->exists());
        $this->assertTrue(Truck::query()->withTrashed()->where('id', $id)->exists());
    }

    /** @test */
    public function success_delete_force()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Truck::query()->where('id', $id)->exists());
        $this->assertFalse(Truck::query()->withTrashed()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_has_open_and_deleted_orders()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder->deleted()->vehicle($model)->create();
        $this->orderBuilder->vehicle($model)->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => $model->id]));

        $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_truck_filter_url'));
        $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_truck_filter_url'));

        $msg = __("exceptions.vehicles.truck.has_open_and_deleted_orders", [
            'open_orders' => $openOrderLink,
            'deleted_orders' => $deleteOrderLink,
        ]);

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_open_orders()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder->vehicle($model)->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => $model->id]));

        $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_truck_filter_url'));

        $msg = __("exceptions.vehicles.truck.has_open_orders", [
            'open_orders' => $openOrderLink,
        ]);

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_deleted_orders()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder->deleted()->vehicle($model)->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => $model->id]));

        $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_truck_filter_url'));

        $msg = __("exceptions.vehicles.truck.has_deleted_orders", [
            'deleted_orders' => $deleteOrderLink,
        ]);

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.vehicles.truck.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
