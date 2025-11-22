<?php

namespace Tests\Feature\Queries\BackOffice\Content\OurCases;

use App\GraphQL\Queries\BackOffice\Content\OurCases\OurCaseCategoriesQuery;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCasesCategoriesTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = OurCaseCategoriesQuery::NAME;

    public function test_categories_list(): void
    {
        $this->loginAsSuperAdmin();

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
            select: $this->getSelect()
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'slug',
            'translation' => [
                'title',
                'description',
                'language',
            ],
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                self::QUERY => [
                    $this->getSelect()
                ],
            ],
        ];
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsSuperAdmin();

        OurCaseCategory::factory()
            ->times(6)
            ->sequence(
                [
                    'active' => true,
                ],
                [
                    'active' => false,
                ]
            )
            ->has(
                OurCaseCategoryTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'active' => true
            ],
            $this->getSelect(),
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertJsonCount(3, 'data.' . self::QUERY)
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    public function test_unauthorized(): void
    {
        $query = new GraphQLQuery(
            self::QUERY,
            [
                'active' => true
            ],
            $this->getSelect(),
        );

        $this->assertServerError(
            $this->postGraphQLBackOffice($query->getQuery()),
            'Unauthorized'
        );
    }
}
