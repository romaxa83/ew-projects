<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Tickets;

use App\Enums\Tickets\TicketStatusEnum;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Catalog\Tickets\TicketTranslation;
use App\Models\Orders\Categories\OrderCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_exists(): void
    {
        $this->loginAsModerator();

        $ticket = Ticket::factory()
            ->has(TicketTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->getJson(route('1c.tickets.exists', $ticket->guid))
            ->assertJson(
                [
                    'exists' => true
                ]
            );

        $this->getJson(route('1c.tickets.exists', 'abcdef'))
            ->assertJson(
                [
                    'exists' => false
                ]
            );
    }

    public function test_create(): void
    {
        $this->loginAsModerator();

        $this->postJson(
            route('1c.tickets.store'),
            array_merge(
                $this->getData(),
                ['guid' => $this->faker->uuid]
            )
        )
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'case_id' => null
                ]
            ])

        ;
    }

    public function test_create_with_case_id(): void
    {
        $this->loginAsModerator();

        $caseID = 9999;

        $this->postJson(
            route('1c.tickets.store'),
            array_merge(
                $this->getData(),
                [
                    'guid' => $this->faker->uuid,
                    'case_id' => $caseID,
                ]
            )
        )
            ->assertCreated()
            ->assertJson([
                'data' => [
                    'case_id' => $caseID
                ]
            ])
        ;
    }

    protected function getData(): array
    {
        return [
            'serial_number' => ProductSerialNumber::factory()->create()->serial_number,
            'status' => TicketStatusEnum::DONE,
            'code' => 'uniquecode',
            'order_parts' => [
                $this->faker->word,
                $this->faker->word,
                $this->faker->word,
            ],
            'translations' => [
                [
                    'title' => 'en title',
                    'description' => 'en description',
                    'language' => 'en',
                ],
                [
                    'title' => 'es title',
                    'description' => 'es description',
                    'language' => 'es',
                ]
            ],
        ];
    }

    public function test_create_new_version_of_order_parts(): void
    {
        $this->loginAsModerator();

        $data = $this->getData();

        unset($data['order_parts']);

        $data['order_parts'] = [
            [
                'guid' => OrderCategory::factory()->create()->guid,
                'value' => $this->faker->word,
            ],
            [
                'guid' => OrderCategory::factory()->create()->guid,
                'value' => $this->faker->word,
            ]
        ];

        $this->postJson(
            route('1c.tickets.store'),
            array_merge(
                $data,
                ['guid' => $this->faker->uuid]
            )
        )
            ->assertCreated();
    }

    public function test_create_empty_ticket(): void
    {
        $this->loginAsModerator();

        $this->postJson(
            route('1c.tickets.store'),
            [
                'guid' => $this->faker->uuid,
                'code' => $this->faker->bothify,
                'serial_number' => ProductSerialNumber::factory()->create()->serial_number,
                'status' => TicketStatusEnum::DONE,
                'translations' => [
                    [
                        'title' => '',
                        'description' => '',
                        'language' => 'en',
                    ],
                    [
                        'language' => 'es',
                    ]
                ],
            ]
        )->assertCreated();
    }

    public function test_update(): void
    {
        $this->loginAsModerator();

        $caseID = 989898;
        $ticket = Ticket::factory()
            ->has(TicketTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getData();
        $data['case_id'] = $caseID;
        unset($data['guid']);

        $this->assertNull($ticket->case_id);

        $this->putJson(route('1c.tickets.update', $ticket->guid), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'case_id' => $caseID
                ]
            ])
        ;
    }

    public function test_update_new_version_of_order_parts(): void
    {
        $this->loginAsModerator();

        $caseID_1 = 12121;
        $caseID_2 = 121213;

        $ticket = Ticket::factory()
            ->has(TicketTranslation::factory()->allLocales(), 'translations')
            ->create(['case_id' => $caseID_1]);

        $data = $this->getData();
        $data['case_id'] = $caseID_2;

        unset($data['guid'], $data['order_parts']);

        $data['order_parts'] = [
            [
                'guid' => OrderCategory::factory()->create()->guid,
                'value' => $this->faker->word,
            ],
            [
                'guid' => OrderCategory::factory()->create()->guid,
                'value' => $this->faker->word,
            ]
        ];

        self::assertTrue($ticket->orderPartsRelation->isEmpty());
        $this->assertNotEquals($ticket->case_id, $caseID_2);

        $this->putJson(route('1c.tickets.update', $ticket->guid), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'case_id' => $caseID_2
                ]
            ])
        ;

        self::assertTrue($ticket->fresh()->orderPartsRelation->isNotEmpty());
    }

    public function test_delete(): void
    {
        $this->loginAsModerator();

        $ticket = Ticket::factory()->create();

        $this->deleteJson(route('1c.tickets.destroy', $ticket->guid))
            ->assertOk();

        $this->assertModelMissing($ticket);
    }

    public function test_get_new(): void
    {
        $this->loginAsModerator();

        Ticket::factory()
            ->times(10)
            ->byTechnician()
            ->create();

        $this->getJson(route('1c.tickets.new', ['per_page' => 4]))
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_get_without_code(): void
    {
        $this->loginAsModerator();

        Ticket::factory()
            ->times(10)
            ->byTechnician()
            ->create(
                [
                    'code' => null
                ]
            );

        $this->getJson(route('1c.tickets.withoutCode', ['per_page' => 4]))
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_update_nullable_code(): void
    {
        $this->loginAsModerator();

        $ticket = Ticket::factory()
            ->create(
                [
                    'code' => null
                ]
            );

        $this->assertCodeUpdated($ticket, 'new-code');
    }

    public function assertCodeUpdated(
        Ticket $ticket,
        string $code
    ): void {
        $this->postJson(
            route('1c.tickets.update-code', $ticket->guid),
            compact('code')
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'id' => $ticket->id,
                        'code' => $code,
                    ],
                ]
            );
    }

    public function test_update_code(): void
    {
        $this->loginAsModerator();

        $this->assertCodeUpdated(Ticket::factory()->create(), 'new-code');
    }
}
