<?php

namespace Tests\Feature\Queries\BackOffice\Chat\Participants;

use App\Models\Technicians\Technician;
use Core\Chat\GraphQL\Queries\Participants\BaseParticipantQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Chat\InteractsWithChatHelper;

class ParticipantQueryTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithChatHelper;

    public const QUERY = BaseParticipantQuery::NAME;

    public function test_get_participants_list(): void
    {
        $admin = $this->loginAsAdmin();

        $c = $this->createConversation($admin);

        $technician = Technician::factory()->create();

        $c->addParticipants($technician);

        $query = $this->getParticipationQuery(
            [
                'conversation_id' => $c->id,
            ]
        );

        $this->postGraphQLBackOffice($query)
            ->assertJsonStructure(
                [
                    'data' => [
                        static::QUERY => [
                            $this->getParticipationSelect()
                        ],
                    ],
                ]
            );
    }
}
