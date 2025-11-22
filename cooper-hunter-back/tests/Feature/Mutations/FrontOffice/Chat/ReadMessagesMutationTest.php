<?php

namespace Tests\Feature\Mutations\FrontOffice\Chat;

use App\GraphQL\Mutations\FrontOffice\Chat\ReadMessagesMutation;
use App\Models\Admins\Admin;
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

        $technician = $this->loginAsTechnicianWithRole();
        $admin = Admin::factory()->create();

        $c = Chat::conversation()
            ->participants($admin, $technician)
            ->start();

        Chat::message('from admin to technician')
            ->from($admin)
            ->to($c)
            ->send();

        /** @var Message $message */
        $message = Chat::messages()
            ->forParticipant($technician)
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

        $this->postGraphQL($query)
            ->assertJsonPath('data.' . self::MUTATION, 1);

        /** @var Message $message */
        $message = Chat::messages()
            ->forParticipant($technician)
            ->forConversation($c)
            ->paginate()[0];

        self::assertEquals(1, $message->messageNotifications[0]->is_seen);
    }
}
