<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Tickets;

use App\Enums\Tickets\TicketStatusEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductQuery;
use App\GraphQL\Queries\FrontOffice\Catalog\Tickets\TicketQuery;
use App\GraphQL\Types\Catalog\Tickets\TicketTranslationType;
use App\GraphQL\Types\Catalog\Tickets\TicketType;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Technicians\Technician;
use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const QUERY = ProductQuery::NAME;

    private string $serialNumber;

    private array $actualResponse;

    public function setUp(): void
    {
        parent::setUp();

        $this->serialNumber = $this->faker->lexify;

        Product::factory()
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

        $this->actualResponse = Ticket::query()
            ->orderByDesc('id')
            ->get()
            ->map(
                fn(Ticket $ticket) => [
                    '__typename' => TicketType::NAME,
                    'id' => $ticket->id,
                    'order_parts' => [],
                    'status' => TicketStatusEnum::DONE,
                    'translation' => [
                        '__typename' => TicketTranslationType::NAME,
                        'id' => $ticket->translation->id,
                        'language' => $ticket->translation->language,
                        'title' => $ticket->translation->title,
                        'description' => $ticket->translation->description
                    ]
                ]
            )
            ->values()
            ->toArray();
    }

    public function test_get_tickets(): void
    {
        $technician = Technician::factory()
            ->certified()
            ->create();

        $this->loginAsTechnicianWithRole($technician);

        $this->postGraphQL(
            GraphQLQuery::query(
                TicketQuery::NAME
            )
                ->args(
                    [
                        'serial_number' => $this->serialNumber
                    ]
                )
                ->select(
                    [
                        'data' => [
                            '__typename',
                            'id',
                            'order_parts',
                            'status',
                            'code',
                            'can_create_order',
                            'translation' => [
                                '__typename',
                                'id',
                                'language',
                                'title',
                                'description'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TicketQuery::NAME => [
                            'data' => $this->actualResponse
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . TicketQuery::NAME . '.data');
    }

    public function test_get_tickets_different_product()
    {
        Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->has(
                ProductSerialNumber::factory(
                    [
                        'serial_number' => $serial = $this->faker->lexify
                    ]
                ),
                'serialNumbers'
            )
            ->create();

        Ticket::factory()
            ->create(
                [
                    'serial_number' => $serial
                ]
            );

        $technician = Technician::factory()
            ->certified()
            ->create();

        $this->loginAsTechnicianWithRole($technician);

        $this->postGraphQL(
            GraphQLQuery::query(
                TicketQuery::NAME
            )
                ->args(
                    [
                        'serial_number' => $this->serialNumber
                    ]
                )
                ->select(
                    [
                        'data' => [
                            '__typename',
                            'id',
                            'order_parts',
                            'status',
                            'translation' => [
                                '__typename',
                                'id',
                                'language',
                                'title',
                                'description'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TicketQuery::NAME => [
                            'data' => $this->actualResponse
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . TicketQuery::NAME . '.data');
    }

    public function test_try_to_get_ticket_by_non_certified_technician(): void
    {
        $technician = Technician::factory()
            ->create();

        $this->loginAsTechnicianWithRole($technician);

        $this->postGraphQL(
            GraphQLQuery::query(
                TicketQuery::NAME
            )
                ->args(
                    [
                        'serial_number' => $this->serialNumber
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => AuthorizationMessageEnum::NO_PERMISSION
                        ]
                    ]
                ]
            );
    }

}
