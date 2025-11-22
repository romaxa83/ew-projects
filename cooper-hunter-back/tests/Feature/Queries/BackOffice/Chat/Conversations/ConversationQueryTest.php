<?php

namespace Tests\Feature\Queries\BackOffice\Chat\Conversations;

use App\Enums\Chat\ConversationTabEnum;
use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use Core\Chat\GraphQL\Queries\Conversations\BaseConversationQuery;
use Core\Chat\Models\Conversation;
use Core\Chat\Models\Participation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Chat\InteractsWithChatHelper;

class ConversationQueryTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithChatHelper;

    public const QUERY = BaseConversationQuery::NAME;

    public function test_list(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $c = Conversation::factory()
            ->has(
                Participation::factory()->forUser($admin),
                'participants'
            )
            ->create();

        $technician = Technician::factory()->create();

        $c->addParticipants($technician);

        $this->sendMessage($admin, $c);

        $query = $this->getConversationQuery(
            [
                'tab' => ConversationTabEnum::MY()
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertJsonCount(1, 'data.' . static::QUERY . '.data');
    }

    public function test_search_by_technician_in_tab(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $c = Conversation::factory()
            ->has(
                Participation::factory()->forUser($admin),
                'participants'
            )
            ->create();

        $technician = Technician::factory()
            ->state(
                [
                    'first_name' => $query = 'technic'
                ]
            )
            ->create();

        $c->addParticipants($technician);

        Conversation::factory()
            ->times(5)
            ->has(
                Participation::factory()
                    ->forUser(Admin::factory()->create()),
                'participants'
            )
            ->create();

        $query = $this->getConversationQuery(
            [
                'tab' => ConversationTabEnum::MY(),
                'query' => $query,
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertJsonCount(1, 'data.' . static::QUERY . '.data');
    }
}
