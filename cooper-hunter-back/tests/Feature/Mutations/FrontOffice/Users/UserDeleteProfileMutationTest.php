<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\Events\Members\MemberProfileDeletedEvent;
use App\GraphQL\Mutations\FrontOffice\Users\UserDeleteProfileMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserDeleteProfileMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UserDeleteProfileMutation::NAME;

    public function test_delete_own_profile(): void
    {
        Event::fake();

        $user = $this->loginAsUser();

        $query = new GraphQLQuery(self::MUTATION);

        self::assertNull($user->deleted_at);

        $this->postGraphQL($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        self::assertNotNull($user->fresh()->deleted_at);

        Event::assertDispatched(MemberProfileDeletedEvent::class);
    }
}
