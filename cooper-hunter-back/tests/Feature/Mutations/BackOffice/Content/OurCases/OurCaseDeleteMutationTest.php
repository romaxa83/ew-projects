<?php

namespace Tests\Feature\Mutations\BackOffice\Content\OurCases;

use App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseDeleteMutation;
use App\Models\Content\OurCases\OurCase;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCaseDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OurCaseDeleteMutation::NAME;

    public function test_delete(): void
    {
        $this->loginAsSuperAdmin();

        $ourCase = OurCase::factory()
            ->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $ourCase->id,
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertDatabaseMissing(
            OurCase::TABLE,
            [
                'id' => $ourCase->id,
            ]
        );
    }
}
