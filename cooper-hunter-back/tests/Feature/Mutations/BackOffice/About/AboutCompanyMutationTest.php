<?php

namespace Tests\Feature\Mutations\BackOffice\About;

use App\GraphQL\Mutations\BackOffice\About\AboutCompanyMutation;
use App\Models\About\AboutCompany;
use App\Models\About\AboutCompanyTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AboutCompanyMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = AboutCompanyMutation::NAME;

    public function test_update_about_company(): void
    {
        AboutCompany::factory()
            ->has(AboutCompanyTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->test_create_about_company();

        $this->assertDatabaseCount(AboutCompany::TABLE, 1);
    }

    public function test_create_about_company(): void
    {
        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'about_company' => [
                    'translations' => [
                        [
                            'language' => 'en',
                            'video_link' => $this->faker->imageUrl,
                            'title' => 'en title',
                            'description' => 'en description',
                            'short_description' => 'en short description',
                            'seo_title' => 'seo_title',
                            'seo_description' => 'seo_description',
                            'seo_h1' => 'seo_h1',
                            'additional_title' => 'additional_title',
                            'additional_description' => 'additional_description',
                            'additional_video_link' => $this->faker->imageUrl,
                        ],
                        [
                            'language' => 'es',
                            'video_link' => $this->faker->imageUrl,
                            'title' => 'es title',
                            'description' => 'es description',
                            'short_description' => 'es short description',
                            'seo_title' => 'seo_title2',
                            'seo_description' => 'seo_description2',
                            'seo_h1' => 'seo_h12',
                            'additional_title' => 'additional_title',
                            'additional_description' => 'additional_description',
                            'additional_video_link' => $this->faker->imageUrl,
                        ]
                    ],
                ],
            ],
            [
                'translation' => [
                    'id',
                    'video_link',
                    'title',
                    'description',
                    'short_description',
                    'language',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                    'additional_title',
                    'additional_description',
                    'additional_video_link',
                ],
                'translations' => [
                    'id',
                    'video_link',
                    'title',
                    'description',
                    'short_description',
                    'language',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'translation' => [
                                'id',
                                'video_link',
                                'title',
                                'description',
                                'short_description',
                                'language',
                                'seo_title',
                                'seo_description',
                                'seo_h1',
                                'additional_title',
                                'additional_description',
                                'additional_video_link',
                            ],
                            'translations' => [
                                [
                                    'id',
                                    'video_link',
                                    'title',
                                    'description',
                                    'short_description',
                                    'language',
                                    'seo_title',
                                    'seo_description',
                                    'seo_h1',
                                ]
                            ],
                        ]
                    ],
                ]
            );
    }
}
