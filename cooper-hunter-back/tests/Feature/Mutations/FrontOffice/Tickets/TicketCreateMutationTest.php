<?php

namespace Tests\Feature\Mutations\FrontOffice\Tickets;

use App\GraphQL\Mutations\FrontOffice\Tickets\TicketCreateMutation;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Orders\Categories\OrderCategory;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\Fake\Http\Tickets\TicketFake;
use Tests\TestCase;

class TicketCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = TicketCreateMutation::NAME;

    public function test_create_ticket(): void
    {
        Http::fake(
            [
                '*' => Http::response(
                    TicketFake::successCreate()
                )
            ]
        );

        $this->loginAsTechnicianWithRole();

        $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'input' => [
                            'serial_number' => ProductSerialNumber::factory()->create()->serial_number,
                            'comment' => 'comment for ticket',
                            'order_parts' => [
                                [
                                    'id' => OrderCategory::factory()->create()->id
                                ],
                                [
                                    'id' => OrderCategory::factory()->create()->id
                                ],
                                [
                                    'id' => OrderCategory::factory()->needsDescription()->create()->id,
                                    'description' => 'some description for ticket'
                                ]
                            ],
                            'translations' => [
                                [
                                    'title' => 'title',
                                    'description' => 'description',
                                    'language' => 'en',
                                ]
                            ],
                        ]
                    ]
                )
                ->select(
                    $this->getSelect()
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => $this->getSelect(true),
                    ],
                ]
            );
    }

    public function getSelect(bool $forJson = false): array
    {
        $parts = [
            'id',
            'name',
            'quantity',
            'description',
        ];

        return [
            'id',
            'status',
            'code',
            'order_parts',
            'order_parts_relation' => $forJson ? [$parts] : $parts,
            'translation' => [
                'language',
                'title',
                'description',
            ],
        ];
    }
}
