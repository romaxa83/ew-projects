<?php

namespace Tests\Feature\Mutations\BackOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Mutations\BackOffice\Chat\ChatConversationStartMutation;
use App\Models\Chat\Conversation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatConversationStartMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_start_conversation(): void
    {
        Event::fake();

        $this->loginAsSuperAdmin();

        $conversation = Conversation::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatConversationStartMutation::NAME)
                ->args(
                    [
                        'id' => $conversation->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatConversationStartMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Conversation::class,
            [
                'id' => $conversation->id,
                'can_messaging' => true
            ]
        );

        Event::assertDispatched(ConversationIsProcessed::class);
    }
}
