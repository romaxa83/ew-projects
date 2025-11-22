<?php

namespace Tests\Feature\Mutations\BackOffice\Faq;

use App\GraphQL\Mutations\BackOffice\Faq\FaqCreateMutation;
use App\Models\Faq\FaqTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FaqCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = FaqCreateMutation::NAME;

    public function test_create_faq(): void
    {
        $this->loginAsSuperAdmin();

        $translationEn = [
            'language' => 'en',
            'question' => 'If there is a question mark at the end of a sentence, is that a question?',
            'answer' => 'Yes.',
            'seo_title' => 'custom seo title en',
            'seo_description' => 'custom seo description en',
            'seo_h1' => 'custom seo h1 en',
        ];

        $translationEs = [
            'language' => 'es',
            'question' => 'Si hay un signo de interrogación al final de una oración, ¿es eso una pregunta?',
            'answer' => 'Si.',
            'seo_title' => 'custom seo title es',
            'seo_description' => 'custom seo description es',
            'seo_h1' => 'custom seo h1 es',
        ];

        $this->assertDatabaseMissing(FaqTranslation::TABLE, $translationEn);
        $this->assertDatabaseMissing(FaqTranslation::TABLE, $translationEs);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'faq' => [
                    'active' => true,
                    'translations' => [
                        $translationEn,
                        $translationEs,
                    ],
                ],
            ],
            [
                'id',
                'sort',
                'active',
                'translation' => [
                    'language',
                    'question',
                    'answer',
                    'seo_title',
                    'seo_description',
                    'seo_h1'
                ],
                'translations' => [
                    'language',
                    'question',
                    'answer',
                    'seo_title',
                    'seo_description',
                    'seo_h1'
                ],
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'sort',
                            'active',
                            'translation' => [
                                'language',
                                'question',
                                'answer',
                                'seo_title',
                                'seo_description',
                                'seo_h1'
                            ],
                            'translations' => [
                                [
                                    'language',
                                    'question',
                                    'answer',
                                    'seo_title',
                                    'seo_description',
                                    'seo_h1'
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->assertDatabaseHas(FaqTranslation::TABLE, $translationEn);
        $this->assertDatabaseHas(FaqTranslation::TABLE, $translationEs);
    }
}
