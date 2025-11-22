<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Catalog;

use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class VehiclesTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;
    protected TruckBuilder $truckBuilder;
    protected CustomerBuilder $customerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    /** @test */
    public function success_search_by_vin()
    {
        $this->loginUserAsSuperAdmin();

        $trailer_1 = $this->trailerBuilder->vin('1111111')->create();
        $truck_1 = $this->truckBuilder->vin('11111112')->create();
        $truck_2 = $this->truckBuilder->vin('11111113')->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'search' => '11111'
        ]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'vin',
                        'make',
                        'model',
                        'year',
                        'vehicle_form',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    [
                        'id' => $trailer_1->id,
                        'vehicle_form' => 'trailer'
                    ],
                    [
                        'id' => $truck_1->id,
                        'vehicle_form' => 'truck'
                    ],
                    [
                        'id' => $truck_2->id,
                        'vehicle_form' => 'truck'
                    ]
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_search_by_unit_number()
    {
        $this->loginUserAsSuperAdmin();

        $this->trailerBuilder->unit_number('1111111')->create();
        $this->truckBuilder->unit_number('11111112')->create();
        $this->truckBuilder->unit_number('11111113')->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'search' => '11111'
        ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_search_by_unit_number_limit()
    {
        $this->loginUserAsSuperAdmin();

        $this->trailerBuilder->unit_number('1111111')->create();
        $this->truckBuilder->unit_number('11111112')->create();
        $this->truckBuilder->unit_number('11111113')->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'search' => '11111',
            'limit' => 2
        ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_search_by_license_plate()
    {
        $this->loginUserAsSuperAdmin();

        $this->trailerBuilder->license_plate('1111111')->create();
        $this->truckBuilder->license_plate('11111112')->create();
        $this->truckBuilder->license_plate('11111113')->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'search' => '11111'
        ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_search_by_temporary_plate()
    {
        $this->loginUserAsSuperAdmin();

        $this->trailerBuilder->temporary_plate('1111111')->create();
        $this->truckBuilder->temporary_plate('11111112')->create();
        $this->truckBuilder->temporary_plate('11111113')->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'search' => '11111'
        ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_search_by_customer()
    {
        $this->loginUserAsSuperAdmin();

        $customer_1 = $this->customerBuilder->first_name('ZZZZ')->create();
        $customer_2 = $this->customerBuilder->first_name('ZZZZ')->create();
        $customer_3 = $this->customerBuilder->last_name('ZZZZ')->create();

        $this->trailerBuilder->customer($customer_1)->create();
        $this->truckBuilder->customer($customer_2)->create();
        $this->truckBuilder->customer($customer_3)->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'search' => 'ZZZZ'
        ]))
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_search_by_id()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->trailerBuilder->create();
        $this->truckBuilder->create();
        $this->truckBuilder->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'id' => $m_1->id
        ]))
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_search_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->trailerBuilder->create();
        $this->truckBuilder->create();
        $this->truckBuilder->create();
        $this->truckBuilder->create();
        $this->trailerBuilder->create();


        $this->getJson(route('api.v1.vehicles.vehicles', [
            'id' => 0
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_search_limit()
    {
        $this->loginUserAsSuperAdmin();

        Truck::factory(['unit_number' => 1111111])->count(40)->create();

        $this->getJson(route('api.v1.vehicles.vehicles', [
            'search' => '11111',
        ]))
            ->assertJsonCount(20, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.vehicles.vehicles', ['id' => 0]));

        self::assertUnauthenticatedMessage($res);
    }
}
