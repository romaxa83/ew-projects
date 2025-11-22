<?php


namespace Tests\Feature\Mutations\BackOffice\Chat;


use App\GraphQL\Mutations\BackOffice\Chat\ChatConversationClosedMutation;
use App\Models\Chat\Conversation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChatConversationClosedMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_off_conversation(): void
    {
        $this->loginAsSuperAdmin();

        $conversation = Conversation::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatConversationClosedMutation::NAME)
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
                        ChatConversationClosedMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Conversation::class,
            [
                'id' => $conversation->id,
                'is_closed' => true
            ]
        );
    }

    public function test_on_conversation(): void
    {
        $this->loginAsSuperAdmin();

        $conversation = Conversation::factory(['is_closed' => true])
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatConversationClosedMutation::NAME)
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
                        ChatConversationClosedMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Conversation::class,
            [
                'id' => $conversation->id,
                'is_closed' => true
            ]
        );
    }
}
