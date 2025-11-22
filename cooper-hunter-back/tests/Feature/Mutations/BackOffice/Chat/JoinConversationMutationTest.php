<?php

namespace Tests\Feature\Mutations\BackOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Mutations\BackOffice\Chat\JoinConversationMutation;
use App\Models\Technicians\Technician;
use Core\Chat\Events\ConversationStarted;
use Core\Chat\Events\MessageWasSent;
use Core\Chat\Events\ParticipantJoined;
use Core\Chat\Facades\Chat;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class JoinConversationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = JoinConversationMutation::NAME;

    public function test_join_conversation(): void
    {
        Event::fake();

        $this->loginAsSuperAdmin();

        $technician = Technician::factory()->create();

        $conversation = Chat::conversation()
            ->start()
            ->addParticipants($technician);

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'conversation_id' => $conversation->id,
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertJsonPath('data.' . self::MUTATION, true);

        Event::assertDispatched(ConversationStarted::class);
        Event::assertDispatched(ConversationIsProcessed::class);
        Event::assertDispatched(ParticipantJoined::class);
        Event::assertNotDispatched(MessageWasSent::class);
    }
}