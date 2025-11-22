<?php

namespace Tests\Feature\Queries\BackOffice\Content\OurCases;

use App\GraphQL\Queries\BackOffice\Content\OurCases\OurCasesQuery;
use App\Models\Content\OurCases\OurCase;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use App\Models\Content\OurCases\OurCaseTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCasesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = OurCasesQuery::NAME;

    public function test_our_cases_list(): void
    {
        $this->loginAsSuperAdmin();

        $category = OurCaseCategory::factory()
            ->has(
                OurCaseCategoryTranslation::factory()->allLocales(),
                'translations'
            )->create();

        OurCase::factory()
            ->times(5)
            ->has(
                OurCaseTranslation::factory()->allLocales(),
                'translations'
            )
            ->for($category, 'category')
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'our_case_category_id' => $category->id,
            ],
            [
                'data' => [
                    'id',
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data');
    }
}
