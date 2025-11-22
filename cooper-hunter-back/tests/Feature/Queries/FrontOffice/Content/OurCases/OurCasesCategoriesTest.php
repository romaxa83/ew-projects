<?php

namespace Tests\Feature\Queries\FrontOffice\Content\OurCases;

use App\GraphQL\Queries\FrontOffice\Content\OurCases\OurCaseCategoriesQuery;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCasesCategoriesTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = OurCaseCategoriesQuery::NAME;

    public function test_home_categories(): void
    {
        OurCaseCategory::factory()
            ->times(6)
            ->has(
                OurCaseCategoryTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'id',
                'slug',
                'cases_count',
                'translation' => [
                    'title',
                    'description',
                    'language',
                ],
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'slug',
                                'translation' => [
                                    'title',
                                    'description',
                                    'language',
                                ],
                            ]
                        ],
                    ],
                ]
            );
    }
}
