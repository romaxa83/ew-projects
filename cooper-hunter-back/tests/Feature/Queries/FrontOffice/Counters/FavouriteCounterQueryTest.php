<?php

namespace Tests\Feature\Queries\FrontOffice\Counters;

use App\GraphQL\Queries\FrontOffice\Counters\FavouriteCounterQuery;
use App\Models\Catalog\Favourites\Favourite;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FavouriteCounterQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = FavouriteCounterQuery::NAME;

    public function test_user_favourites(): void
    {
        $user = $this->loginAsUser();

        Favourite::factory()
            ->times(5)
            ->forUser($user)
            ->create();

        Favourite::factory()
            ->times(5)
            ->create();

        $query = new GraphQLQuery(self::QUERY);

        $this->postGraphQL($query->getQuery())
            ->assertJsonPath('data.' . self::QUERY, 5);
    }

    public function test_technician_favourites(): void
    {
        $technician = $this->loginAsTechnician();

        Favourite::factory()
            ->times(5)
            ->forTechnician($technician)
            ->create();

        Favourite::factory()
            ->times(5)
            ->create();

        $query = new GraphQLQuery(self::QUERY);

        $this->postGraphQL($query->getQuery())
            ->assertJsonPath('data.' . self::QUERY, 5);
    }
}
