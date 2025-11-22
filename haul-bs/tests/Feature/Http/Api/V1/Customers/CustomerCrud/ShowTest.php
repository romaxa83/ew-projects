<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerCrud;

use App\Models\Customers\Customer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected AddressBuilder $addressBuilder;
    protected TagBuilder $tagBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->tagBuilder = resolve(TagBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->addressBuilder = resolve(AddressBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        $t = $this->tagBuilder->create();

        /** @var $m Customer */
        $m = $this->customerBuilder->tags($t)->create();

        $address = $this->addressBuilder->customer($m)->create();

        $truck = $this->truckBuilder->customer($m)->create();
        $trailer = $this->trailerBuilder->customer($m)->create();

        $this->orderBuilder->customer($m)->create();

        $this->getJson(route('api.v1.customers.show', ['id' => $m->id]))
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'phone_extension',
                    'phones',
                    'notes',
                    'tags' => [
                        [
                            'id',
                            'name',
                            'color',
                        ]
                    ],
                    'hasRelatedEntities',
                    'hasRelatedPartOrders',
                    'trucks' => [
                        [
                            'id',
                            'vin',
                            'unit_number',
                            'license_plate',
                            'make',
                            'model',
                            'year',
                            'type',
                            'tags',
                        ]
                    ],
                    'trailers' => [
                        [
                            'id',
                            'vin',
                            'unit_number',
                            'license_plate',
                            'make',
                            'model',
                            'year',
                            'type',
                            'tags',
                        ]
                    ],
                    'type',
                    'addresses' => [
                        [
                            'id',
                            'is_default',
                            'from_ecomm',
                            'first_name',
                            'last_name',
                            'company_name',
                            'address',
                            'city',
                            'state',
                            'zip',
                            'phone',
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $m->id,
                    'hasRelatedEntities' => true,
                    'hasRelatedPartOrders' => true,
                    'trailers' => [
                        ['id' => $trailer->id]
                    ],
                    'trucks' => [
                        ['id' => $truck->id]
                    ],
                    'addresses' => [
                        ['id' => $address->id]
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_sort_addresses()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Customer */
        $m = $this->customerBuilder->create();

        $address_1 = $this->addressBuilder->customer($m)->sort('10')->create();
        $address_2 = $this->addressBuilder->customer($m)->sort('11')->create();
        $address_3 = $this->addressBuilder->customer($m)->sort('1')->create();
        $address_4 = $this->addressBuilder->customer($m)->sort('22')->create();

        $this->getJson(route('api.v1.customers.show', ['id' => $m->id]))
            ->assertJson([
                'data' => [
                    'addresses' => [
                        ['id' => $address_4->id],
                        ['id' => $address_2->id],
                        ['id' => $address_1->id],
                        ['id' => $address_3->id],
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_without_relation()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Customer */
        $m = $this->customerBuilder->create();

        $this->getJson(route('api.v1.customers.show', ['id' => $m->id]))
            ->assertJson([
                'data' => [
                    'id' => $m->id,
                    'hasRelatedEntities' => false,
                    'hasRelatedPartOrders' => false,
                    'trailers' => [],
                    'trucks' => []
                ],
            ])
            ->assertJsonCount(0, 'data.tags')
            ->assertJsonCount(0, 'data.trucks')
            ->assertJsonCount(0, 'data.trailers')
        ;
    }

    /** @test */
    public function success_show_from_haulk()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m Customer */
        $m = $this->customerBuilder->fromHaulk()->create();

        $this->getJson(route('api.v1.customers.show', ['id' => $m->id]))
            ->assertJson([
                'data' => [
                    'id' => $m->id,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.customers.show', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $m Customer */
        $m = $this->customerBuilder->create();

        $res = $this->getJson(route('api.v1.customers.show', ['id' => $m->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m Customer */
        $m = $this->customerBuilder->create();

        $res = $this->getJson(route('api.v1.customers.show', ['id' => $m->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
