<?php

namespace Tests\Feature\Queries\FrontOffice\Tickets;

use App\GraphQL\Queries\FrontOffice\Tickets\TicketsQuery;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Orders\Categories\OrderCategory;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TicketsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = TicketsQuery::NAME;

    public function test_tickets_list(): void
    {
        $this->loginAsTechnicianWithRole();

        Ticket::factory()
            ->times(5)
            ->state(
                [
                    'serial_number' => $serial = 'some-serial',
                ]
            )
            ->has(
                OrderCategory::factory(),
                'orderPartsRelation'
            )
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(self::QUERY)
                ->args(['serial_number' => $serial])
                ->select(
                    [
                        'data' => [
                            'id',
                            'case_id',
                            'order_parts_relation' => [
                                'id',
                                'name',
                                'quantity',
                                'description',
                            ],
                        ],
                    ]
                )
                ->make(),
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id',
                                    'case_id'
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }
}
