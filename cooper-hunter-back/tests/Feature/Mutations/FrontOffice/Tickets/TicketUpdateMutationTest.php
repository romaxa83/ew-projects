<?php

namespace Tests\Feature\Mutations\FrontOffice\Tickets;

use App\GraphQL\Mutations\FrontOffice\Tickets\TicketUpdateMutation;
use App\Models\Catalog\Tickets\Ticket;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TicketUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = TicketUpdateMutation::NAME;

    public function test_update_ticket(): void
    {
        $this->markTestSkipped('Клиент сказал что не надо обновлять тикеты');

        $this->loginAsTechnicianWithRole();

        $this->makeRequest(Ticket::factory()->byTechnician()->create()->id)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => $this->getSelect(),
                    ],
                ]
            );
    }

    public function makeRequest(int $id): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'id' => $id,
                        'translations' => [
                            [
                                'title' => 'title',
                                'description' => 'description',
                                'language' => 'en',
                            ]
                        ]
                    ]
                )
                ->select(
                    $this->getSelect()
                )
                ->make()
        );
    }

    public function getSelect(): array
    {
        return [
            'id',
            'status',
            'code',
            'order_parts',
            'translation' => [
                'language',
                'title',
                'description',
            ],
        ];
    }

    public function test_could_not_update_ticket_created_by_api(): void
    {
        $this->markTestSkipped('Клиент сказал что не надо обновлять тикеты');

        $this->loginAsTechnicianWithRole();

        $this->assertResponseHasValidationMessage(
            $this->makeRequest(Ticket::factory()->create()->id),
            'id',
            [__('validation.exists', ['attribute' => 'id'])],
        );
    }
}