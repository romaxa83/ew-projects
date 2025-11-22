<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Manuals\Groups;

use App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups\ManualGroupDeleteMutation;
use App\Models\Catalog\Manuals\ManualGroup;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManualGroupDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ManualGroupDeleteMutation::NAME;

    public function test_delete_manual_group(): void
    {
        $this->loginAsSuperAdmin();

        $manualGroup = ManualGroup::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $manualGroup->id,
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonPath('data.' . self::MUTATION, true);
    }
}
