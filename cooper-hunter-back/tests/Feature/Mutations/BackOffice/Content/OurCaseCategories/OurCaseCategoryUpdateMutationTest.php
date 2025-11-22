<?php

namespace Tests\Feature\Mutations\BackOffice\Content\OurCaseCategories;

use App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories\OurCaseCategoryUpdateMutation;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCaseCategoryUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OurCaseCategoryUpdateMutation::NAME;

    public function test_update(): void
    {
        $this->loginAsSuperAdmin();

        $category = OurCaseCategory::factory()
            ->has(
                OurCaseCategoryTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->create();

        $translationEn = [
            'language' => 'en',
            'title' => 'en title',
            'description' => 'en description',
            'seo_title' => 'custom seo title en',
            'seo_description' => 'custom seo description en',
            'seo_h1' => 'custom seo h1 en',
        ];

        $translationEs = [
            'language' => 'es',
            'title' => 'es title',
            'description' => 'es description',
            'seo_title' => 'custom seo title es',
            'seo_description' => 'custom seo description es',
            'seo_h1' => 'custom seo h1 es',
        ];

        $this->assertDatabaseMissing(OurCaseCategoryTranslation::TABLE, $translationEn);
        $this->assertDatabaseMissing(OurCaseCategoryTranslation::TABLE, $translationEs);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'our_case_category' => [
                    'id' => $category->id,
                    'active' => true,
                    'slug' => 'slug',
                    'translations' => [
                        $translationEn,
                        $translationEs,
                    ],
                ],
            ],
            [
                'id',
                'active',
                'sort',
                'slug',
                'translations' => [
                    'language',
                    'title',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1'
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'active',
                            'sort',
                            'slug',
                            'translations' => [
                                [
                                    'language',
                                    'title',
                                    'description',
                                    'seo_title',
                                    'seo_description',
                                    'seo_h1'
                                ]
                            ],
                        ],
                    ],
                ]
            );

        $this->assertDatabaseHas(OurCaseCategoryTranslation::TABLE, $translationEn);
        $this->assertDatabaseHas(OurCaseCategoryTranslation::TABLE, $translationEs);
    }
}
