<?php

namespace Tests\Feature\Mutations\BackOffice\Faq;

use App\GraphQL\Mutations\BackOffice\Faq\FaqToggleActiveMutation;
use App\Models\Faq\Faq;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FaqToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = FaqToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $faq = Faq::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $faq->id,
            ],
            [
                'id',
                'sort',
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
                            'sort',
                            'active',
                        ],
                    ],
                ]
            );
    }
}
