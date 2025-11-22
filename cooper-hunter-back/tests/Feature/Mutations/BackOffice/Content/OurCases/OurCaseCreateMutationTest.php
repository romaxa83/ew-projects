<?php

namespace Tests\Feature\Mutations\BackOffice\Content\OurCases;

use App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseCreateMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\Content\OurCases\OurCaseCategoryTranslation;
use App\Models\Content\OurCases\OurCaseTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCaseCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OurCaseCreateMutation::NAME;

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $product = Product::factory()->create();

        $category = OurCaseCategory::factory()
            ->has(OurCaseCategoryTranslation::factory()->allLocales(), 'translations')
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

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'our_case' => [
                    'our_case_category_id' => $category->id,
                    'product_ids' => [$product->id],
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
                'category' => [
                    'id',
                    'translation' => [
                        'language',
                        'title',
                        'description',
                    ],
                ],
                'products' => [
                    'id'
                ],
            ],
        );

        $this->assertDatabaseMissing(OurCaseTranslation::TABLE, $translationEn);
        $this->assertDatabaseMissing(OurCaseTranslation::TABLE, $translationEs);

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
                            'category' => [
                                'id',
                                'translation' => [
                                    'language',
                                    'title',
                                    'description',
                                ],
                            ],
                            'products' => [
                                [
                                    'id'
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
