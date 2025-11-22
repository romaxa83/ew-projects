<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\FrontOffice\Supports;

use App\GraphQL\Queries\FrontOffice\Supports\SupportQuery;
use App\Models\Support\Supports\Support;
use App\Models\Support\Supports\SupportTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SupportQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = SupportQuery::NAME;

    public function test_get_support_success(): void
    {
        Support::factory()
            ->has(SupportTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->query(
            $structure = [
                'phone',
                'translation' => [
                    'description',
                    'short_description',
                    'working_time',
                    'video_link',
                ],
            ]
        )
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => $structure
                    ]
                ]
            );
    }

    protected function query(array $select): TestResponse
    {
        $query = GraphQLQuery::query(self::QUERY)
            ->select($select);

        return $this->postGraphQL($query->make());
    }
}
