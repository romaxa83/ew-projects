<?php

namespace Tests\Feature\Mutations\BackOffice\Content\OurCases;

use App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseUpdateMutation;
use App\Models\Content\OurCases\OurCase;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use App\Models\Content\OurCases\OurCaseTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCaseUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OurCaseUpdateMutation::NAME;

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

        $ourCase = OurCase::factory()
            ->has(
                OurCaseTranslation::factory()->allLocales(),
                'translations'
            )
            ->for($category, 'category')
            ->create();

        $translationEn = [
            'language' => 'en',
            'title' => 'en title',
            'description' => 'en description',
        ];

        $translationEs = [
            'language' => 'es',
            'title' => 'es title',
            'description' => 'es description',
        ];

        $this->assertDatabaseMissing(OurCaseTranslation::TABLE, $translationEn);
        $this->assertDatabaseMissing(OurCaseTranslation::TABLE, $translationEs);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'our_case' => [
                    'id' => $ourCase->id,
                    'our_case_category_id' => $category->id,
                    'active' => true,
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
                'translations' => [
                    'language',
                    'title',
                    'description',
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
                            'translations' => [
                                [
                                    'language',
                                    'title',
                                    'description',
                                ]
                            ],
                        ],
                    ],
                ]
            );

        $this->assertDatabaseHas(OurCaseTranslation::TABLE, $translationEn);
        $this->assertDatabaseHas(OurCaseTranslation::TABLE, $translationEs);
    }
}
