<?php

namespace Tests\Feature\Mutations\BackOffice\News;

use App\GraphQL\Mutations\BackOffice\News\NewsToggleActiveMutation;
use App\Models\News\News;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NewsToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = NewsToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $news = News::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $news->id,
            ],
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
