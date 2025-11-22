<?php

namespace Tests\Feature\Mutations\FrontOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Mutations\FrontOffice\Chat\ProhibitMessagingMutation;
use App\Models\Chat\Conversation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProhibitMessagingMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_prohibit_messaging(): void
    {
        Event::fake();

        $technician = $this->loginAsTechnicianWithRole();

        $conversation = Conversation::factory()
            ->canMessaging()
            ->create();

        $conversation->addParticipants($technician);

        $this->postGraphQL(
            GraphQLQuery::mutation(ProhibitMessagingMutation::NAME)->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ProhibitMessagingMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Conversation::class,
            [
                'id' => $conversation->id,
                'can_messaging' => false
            ]
        );

        Event::assertDispatched(ConversationIsProcessed::class);
    }
}