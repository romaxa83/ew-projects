<?php

namespace Tests\Feature\Queries\BackOffice\Chat\Messages;

use App\GraphQL\Queries\BackOffice\Chat\ChatTabCountersQuery;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Chat\InteractsWithChatHelper;

class ChatTabCountersQueryTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithChatHelper;

    public const QUERY = ChatTabCountersQuery::NAME;

    public function test_get_conversations_count(): void
    {
        $admin = $this->loginAsAdmin();

        $conversation = $this->createConversation($admin);
        $conversation->addParticipants($technician = Technician::factory()->create());

        $this->sendMessage($admin, $conversation, 'read');
        $this->sendMessage($technician, $conversation, 'unread');
        $this->sendMessage($technician, $conversation, 'unread');

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    'tab',
                    'count',
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'tab',
                                'count',
                            ]
                        ],
                    ]
                ]
            );
    }
}
