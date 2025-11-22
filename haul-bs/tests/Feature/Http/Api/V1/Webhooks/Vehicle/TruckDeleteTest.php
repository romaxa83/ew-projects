<?php

namespace Feature\Http\Api\V1\Webhooks\Vehicle;

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class TruckDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected TruckBuilder $truckBuilder;
    protected OrderBuilder $orderBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $originId = 10;
        $this->truckBuilder->origin_id($originId)->create();

        $this->deleteJson(route('api.v1.webhooks.vehicles.truck.delete', ['id' => $originId]), [], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Delete truck',
                ]
            ])
        ;

        $this->assertFalse(Truck::query()->where('origin_id', $originId)->exists());
        $this->assertFalse(Truck::query()->withTrashed()->where('origin_id', $originId)->exists());
    }

    /** @test */
    public function success_delete_but_not_force()
    {
        $originId = 10;
        $model = $this->truckBuilder->origin_id($originId)->create();

        $this->orderBuilder
            ->vehicle($model)
            ->status(OrderStatus::Finished->value, CarbonImmutable::now()->subDays(3))
            ->create();

        $this->deleteJson(route('api.v1.webhooks.vehicles.truck.delete', ['id' => $originId]), [], [
            'Authorization' => config('api.webhook.token')
        ])
            ->assertJson([
                'data' => [
                    'message' => 'Delete truck',
                ]
            ])
        ;

        $this->assertFalse(Truck::query()->where('origin_id', $originId)->exists());
        $this->assertTrue(Truck::query()->withTrashed()->where('origin_id', $originId)->exists());
    }

    /** @test */
    public function not_delete_has_open_order()
    {
        $originId = 10;
        $model = $this->truckBuilder->origin_id($originId)->create();

        $this->orderBuilder->vehicle($model)->create();

        $res = $this->deleteJson(route('api.v1.webhooks.vehicles.truck.delete', ['id' => $originId]), [], [
            'Authorization' => config('api.webhook.token')
        ])
        ;

        self::assertErrorMsg($res, 'The model is not deleted, there are related entities', Response::HTTP_BAD_REQUEST);

        $this->assertTrue(Truck::query()->where('origin_id', $originId)->exists());
    }

    /** @test */
    public function fail_not_found()
    {
        $originId = 10;

        $res = $this->deleteJson(route('api.v1.webhooks.vehicles.truck.delete', ['id' => $originId]), [], [
            'Authorization' => config('api.webhook.token')
        ])
        ;

        self::assertErrorMsg($res, __("exceptions.vehicles.truck.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_auth()
    {
        $originId = 10;
        $this->truckBuilder->origin_id($originId)->create();

        $res = $this->deleteJson(route('api.v1.webhooks.vehicles.truck.delete', ['id' => $originId]))
        ;

        self::assertErrorMsg($res, "Wrong webhook auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
