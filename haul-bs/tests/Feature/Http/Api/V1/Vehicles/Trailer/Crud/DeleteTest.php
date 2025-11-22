<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Trailer\Crud;

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Vehicles\Trailer;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $this->orderBuilder
            ->vehicle($model)
            ->status(OrderStatus::Finished->value, CarbonImmutable::now()->subDays(3))
            ->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Trailer::query()->where('id', $id)->exists());
        $this->assertTrue(Trailer::query()->withTrashed()->where('id', $id)->exists());
    }

    /** @test */
    public function success_delete_force()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Trailer::query()->where('id', $id)->exists());
        $this->assertFalse(Trailer::query()->withTrashed()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_has_open_and_deleted_orders()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $this->orderBuilder->deleted()->vehicle($model)->create();
        $this->orderBuilder->vehicle($model)->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => $model->id]));

        $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_trailer_filter_url'));
        $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_trailer_filter_url'));

        $msg = __("exceptions.vehicles.trailer.has_open_and_deleted_orders", [
            'open_orders' => $openOrderLink,
            'deleted_orders' => $deleteOrderLink,
        ]);

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_open_orders()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $this->orderBuilder->vehicle($model)->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => $model->id]));

        $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_trailer_filter_url'));

        $msg = __("exceptions.vehicles.trailer.has_open_orders", [
            'open_orders' => $openOrderLink,
        ]);

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_deleted_orders()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $this->orderBuilder->deleted()->vehicle($model)->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => $model->id]));

        $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_trailer_filter_url'));

        $msg = __("exceptions.vehicles.trailer.has_deleted_orders", [
            'deleted_orders' => $deleteOrderLink,
        ]);

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.vehicles.trailer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
