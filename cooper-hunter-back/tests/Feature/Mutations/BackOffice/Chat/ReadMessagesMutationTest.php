<?php

namespace Tests\Feature\Mutations\BackOffice\Chat;

use App\GraphQL\Mutations\BackOffice\Chat\ReadMessagesMutation;
use App\Models\Technicians\Technician;
use Core\Chat\Facades\Chat;
use Core\Chat\Models\Message;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReadMessagesMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ReadMessagesMutation::NAME;

    public function test_read_messages(): void
    {
        Event::fake();

        $admin = $this->loginAsAdmin();
        $technician = Technician::factory()->create();

        $c = Chat::conversation()
            ->participants($admin, $technician)
            ->start();

        Chat::message('from technician to admin')
            ->from($technician)
            ->to($c)
            ->send();

        /** @var Message $message */
        $message = Chat::messages()
            ->forParticipant($admin)
            ->forConversation($c)
            ->paginate()[0];

        self::assertEquals(0, $message->messageNotifications[0]->is_seen);

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'conversation_id' => $c->id,
                    'message_ids' => [$message->id],
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertJsonPath('data.' . self::MUTATION, 1);

        /** @var Message $message */
        $message = Chat::messages()
            ->forParticipant($technician)
            ->forConversation($c)
            ->paginate()[0];

        self::assertEquals(1, $message->messageNotifications[0]->is_seen);
    }
}
