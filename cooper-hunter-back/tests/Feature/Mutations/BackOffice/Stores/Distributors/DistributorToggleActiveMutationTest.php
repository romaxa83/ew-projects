<?php

namespace Tests\Feature\Mutations\BackOffice\Stores\Distributors;

use App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorToggleActiveMutation;
use App\Models\Stores\Distributor;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DistributorToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DistributorToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $id = Distributor::factory()->create()->id;

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
