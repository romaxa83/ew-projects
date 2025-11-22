<?php

namespace Tests\Feature\Mutations\BackOffice\Stores\Stores;

use App\GraphQL\Mutations\BackOffice\Stores\Stores\StoreToggleActiveMutation;
use App\Models\Stores\Store;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StoreToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = StoreToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $id = Store::factory()->create()->id;

        $query = new GraphQLQuery(
            self::MUTATION,
            compact('id'),
            [
                'id',
                'active',
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonPath('data.' . self::MUTATION . '.active', false)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'active',
                        ],
                    ],
                ]
            );
    }
}
