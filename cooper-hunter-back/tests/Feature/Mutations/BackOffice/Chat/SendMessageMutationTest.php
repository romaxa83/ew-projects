<?php

namespace Tests\Feature\Mutations\BackOffice\Chat;

use App\Events\Chat\ConversationIsProcessed;
use App\GraphQL\Mutations\BackOffice\Chat\SendMessageMutation;
use App\Models\Technicians\Technician;
use Core\Chat\Events\ConversationStarted;
use Core\Chat\Events\MessageWasSent;
use Core\Chat\Events\ParticipantJoined;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Message;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SendMessageMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SendMessageMutation::NAME;

    public function test_send_message(): void
    {
        Event::fake();

        $admin = $this->loginAsSuperAdmin();
        $technician = Technician::factory()->create();

        $conversation = Chat::conversation()
            ->start()
            ->addParticipants($technician);

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'conversation_id' => $conversation->id,
                    'text' => $text = 'hello, dude!',
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertJsonPath('data.' . self::MUTATION, true);

        Event::assertDispatched(ConversationStarted::class);
        Event::assertDispatched(ConversationIsProcessed::class);
        Event::assertDispatched(ParticipantJoined::class);
        Event::assertDispatched(MessageWasSent::class);

        $conversation = Chat::conversations()
            ->between($admin, $technician);

        $this->assertDatabaseHas(
            Message::TABLE,
            [
                'body' => $text,
                'conversation_id' => $conversation->id,
            ],
        );
    }
}
