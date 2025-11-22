<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Tickets;

use App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductQuery;
use App\GraphQL\Queries\FrontOffice\Catalog\Search\UnitSearchQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketFlagInUnitsTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const QUERY = ProductQuery::NAME;

    private string $serialNumber;
    private Product $product;

    private array $actualResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->serialNumber = $this->faker->lexify;

        $this->product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->has(
                ProductSerialNumber::factory(
                    [
                        'serial_number' => $this->serialNumber
                    ]
                ),
                'serialNumbers'
            )
            ->create();

        Ticket::factory()
            ->create(
                [
                    'serial_number' => $this->serialNumber
                ]
            );
    }

    public function test_search_product_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->postGraphQL(
            GraphQLQuery::query(UnitSearchQuery::NAME)
                ->args(
                    [
                        'serial_number' => $this->serialNumber
                    ]
                )
                ->select(
                    [
                        'id',
                        'tickets_exists'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UnitSearchQuery::NAME => [
                            'id' => $this->product->id,
                            'tickets_exists' => false
                        ],
                    ]
                ]
            );
    }

    public function test_search_product_by_non_certified_technician(): void
    {
        $technician = Technician::factory()
            ->create();

        $this->loginAsTechnicianWithRole($technician);

        $this->postGraphQL(
            GraphQLQuery::query(UnitSearchQuery::NAME)
                ->args(
                    [
                        'serial_number' => $this->serialNumber
                    ]
                )
                ->select(
                    [
                        'id',
                        'tickets_exists'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UnitSearchQuery::NAME => [
                            'id' => $this->product->id,
                            'tickets_exists' => false
                        ],
                    ]
                ]
            );
    }

    public function test_search_product_by_certified_technician(): void
    {
        $this->loginAsTechnicianWithRole();

        $this->postGraphQL(
            GraphQLQuery::query(UnitSearchQuery::NAME)
                ->args(
                    [
                        'serial_number' => $this->serialNumber
                    ]
                )
                ->select(
                    [
                        'id',
                        'tickets_exists'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UnitSearchQuery::NAME => [
                            'id' => $this->product->id,
                            'tickets_exists' => true
                        ],
                    ]
                ]
            );
    }

}
