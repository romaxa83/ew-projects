<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Truck\Crud;

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Companies\CompanyBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;
    protected TruckBuilder $truckBuilder;
    protected CompanyBuilder $companyBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder->create();
        $company = $this->companyBuilder->create();
        /** @var $model Truck */
        $model = $this->truckBuilder->tags($tag)->company($company)->create();

        $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'vin' => $model->vin,
                    'unit_number' => $model->unit_number,
                    'license_plate' => $model->license_plate,
                    'temporary_plate' => $model->temporary_plate,
                    'make' => $model->make,
                    'model' => $model->model,
                    'year' => $model->year,
                    'type' => $model->type,
                    'notes' => $model->notes,
                    'color' => $model->color,
                    'gvwr' => $model->gvwr,
                    'tags' => [
                        [
                            'id' => $tag->id
                        ]
                    ],
                    'owner' => [
                        'id' => $model->customer_id
                    ],
                    'attachments' => [],
                    'company_name' => $company->name
                ],
            ])
            ->assertJsonCount(0, 'data.attachments')
        ;
    }

    /** @test */
    public function success_show_has_open_order_as_new()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder->vehicle($model)->status(OrderStatus::New->value)->create();

        $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'hasRelatedOpenOrders' => true,
                    'hasRelatedDeletedOrders' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_open_order_as_in_process()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder->vehicle($model)->status(OrderStatus::In_process->value)->create();

        $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'hasRelatedOpenOrders' => true,
                    'hasRelatedDeletedOrders' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_open_order_as_in_finish_now()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder->vehicle($model)
            ->status(OrderStatus::Finished->value, CarbonImmutable::now()->subMinutes(4))
            ->create();

        $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'hasRelatedOpenOrders' => true,
                    'hasRelatedDeletedOrders' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_deleted_order()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $this->orderBuilder->vehicle($model)
            ->deleted()
            ->create();

        $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => true,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.vehicles.truck.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_as_deleted()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Truck */
        $model = $this->truckBuilder->delete()->create();

        $res = $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __("exceptions.vehicles.truck.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $res = $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $res = $this->getJson(route('api.v1.vehicles.trucks.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
