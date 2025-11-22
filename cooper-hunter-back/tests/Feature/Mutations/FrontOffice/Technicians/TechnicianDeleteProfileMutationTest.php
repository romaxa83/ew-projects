<?php

namespace Tests\Feature\Mutations\FrontOffice\Technicians;

use App\Events\Members\MemberProfileDeletedEvent;
use App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianDeleteProfileMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TechnicianDeleteProfileMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = TechnicianDeleteProfileMutation::NAME;

    public function test_delete_own_profile(): void
    {
        Event::fake();

        $technician = $this->loginAsTechnician();

        $query = new GraphQLQuery(self::MUTATION);

        self::assertNull($technician->deleted_at);

        $this->postGraphQL($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        self::assertNotNull($technician->fresh()->deleted_at);

        Event::assertDispatched(MemberProfileDeletedEvent::class);
    }
}
