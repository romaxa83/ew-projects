<?php

namespace Tests\Feature\Queries\BackOffice\Chat\Messages;

use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use Core\Chat\GraphQL\Queries\Messages\BaseMessageQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Chat\InteractsWithChatHelper;

class MessageQueryTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithChatHelper;

    public const QUERY = BaseMessageQuery::NAME;

    public function test_get_messages_list(): void
    {
        $admin = $this->loginAsAdmin();

        $conversation = $this->createConversation($admin);
        $conversation->addParticipants($technician = Technician::factory()->create());

        $this->sendMessage($admin, $conversation, 'hello1');
        $this->sendMessage($technician, $conversation, 'hello2');
        $this->sendMessage($technician, $conversation, 'hello3');

        $query = $this->getQuery(
            [
                'per_page' => 2,
                'conversation_id' => $conversation->id,
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertJsonCount(2, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'body',
                                    'type',
                                    'message_info' => [
                                        'is_seen',
                                        'is_sender',
                                    ],
                                    'participation' => [
                                        'participant' => [
                                            'name',
                                            'email',
                                            'phone',
                                        ],
                                    ],
                                ]
                            ]
                        ]
                    ],
                ]
            );
    }

    protected function getQuery(array $args): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select(
                [
                    'data' => $this->getSelect(),
                ]
            )
            ->make();
    }

    protected function getSelect(): array
    {
        return [
            'body',
            'type',
            'message_info' => [
                'is_seen',
                'is_sender',
            ],
            'participation' => [
                'participant' => [
                    'name',
                    'email',
                    'phone',
                ],
            ],
        ];
    }

    public function test_get_conversation_not_belong_to_admin(): void
    {
        $this->loginAsAdmin();

        $otherAdmin = Admin::factory()->create();

        $conversation = $this->createConversation($otherAdmin);
        $conversation->addParticipants($technician = Technician::factory()->create());

        $this->sendMessage($otherAdmin, $conversation, 'hello1');
        $this->sendMessage($technician, $conversation, 'hello2');

        $query = $this->getQuery(
            [
                'conversation_id' => $conversation->id,
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertJsonCount(2, 'data.' . self::QUERY . '.data');
    }
}
