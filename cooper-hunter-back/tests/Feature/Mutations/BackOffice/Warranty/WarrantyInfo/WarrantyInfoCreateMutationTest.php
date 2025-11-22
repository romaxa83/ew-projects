<?php

namespace Tests\Feature\Mutations\BackOffice\Warranty\WarrantyInfo;

use App\GraphQL\Mutations\BackOffice\Warranty\WarrantyInfo\WarrantyInfoMutation;
use App\Models\Media\Media;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use JsonException;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class WarrantyInfoCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;
    use WithFaker;

    public const MUTATION = WarrantyInfoMutation::NAME;

    /**
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function test_create_warranty_info(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $pdf = $this->getSamplePdf();
        $image = $this->getSampleImage();

        $translationEn = [
            'language' => 'en',
            'description' => 'en description',
            'notice' => 'en notice',
            'seo_title' => 'custom seo title en',
            'seo_description' => 'custom seo description en',
            'seo_h1' => 'custom seo h1 en',
        ];

        $translationEs = [
            'language' => 'es',
            'description' => 'es description',
            'notice' => 'es notice',
            'seo_title' => 'custom seo title es',
            'seo_description' => 'custom seo description es',
            'seo_h1' => 'custom seo h1 es',
        ];

        $this->assertDatabaseMissing(WarrantyInfoTranslation::TABLE, $translationEn);
        $this->assertDatabaseMissing(WarrantyInfoTranslation::TABLE, $translationEs);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'warranty_info' => [
                    'pdf' => $pdf,
                    'video_link' => $this->faker->imageUrl,
                    'translations' => [
                        $translationEn,
                        $translationEs,
                    ],
                    'packages' => [
                        [
                            'image' => $image,
                            'translations' => [
                                [
                                    'language' => 'en',
                                    'title' => 'en title',
                                    'description' => 'en description',
                                ],
                                [
                                    'language' => 'es',
                                    'title' => 'es title',
                                    'description' => 'es description',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id',
                'translation' => [
                    'id',
                    'language',
                    'notice',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ],
                'translations' => [
                    'id',
                    'language',
                    'notice',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ],
            ]
        );

        $id = $this->postGraphQlBackOfficeUpload($query->getUploadMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id'
                        ],
                    ],
                ]
            )->json('data.' . self::MUTATION . '.id');

        $this->assertDatabaseHas(
            Media::TABLE,
            [
                'model_id' => $id,
                'model_type' => WarrantyInfo::MORPH_NAME,
                'mime_type' => $pdf->getMimeType()
            ]
        );

        $this->assertDatabaseHas(WarrantyInfoTranslation::TABLE, $translationEn);
        $this->assertDatabaseHas(WarrantyInfoTranslation::TABLE, $translationEs);
    }

    /**
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function test_update_packages(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $image = $this->getSampleImage();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'warranty_info' => [
                    'video_link' => $this->faker->imageUrl,
                    'translations' => [
                        [
                            'language' => 'en',
                            'description' => 'en description',
                            'notice' => 'en notice',
                        ],
                        [
                            'language' => 'es',
                            'description' => 'es description',
                            'notice' => 'es notice',
                        ],
                    ],
                    'packages' => [
                        [
                            'image' => $image,
                            'translations' => [
                                [
                                    'language' => 'en',
                                    'title' => 'en title',
                                    'description' => 'en description',
                                ],
                                [
                                    'language' => 'es',
                                    'title' => 'es title',
                                    'description' => 'es description',
                                ],
                            ],
                        ],
                        [
                            'translations' => [
                                [
                                    'language' => 'en',
                                    'title' => 'en title 2',
                                    'description' => 'en description 2',
                                ],
                                [
                                    'language' => 'es',
                                    'title' => 'es title 2',
                                    'description' => 'es description 2',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id',
                'translation' => [
                    'id',
                    'language',
                    'notice',
                    'description',
                ],
                'translations' => [
                    'id',
                    'language',
                    'notice',
                    'description',
                ],
                'packages' => [
                    'id',
                    'image' => [
                        'name'
                    ],
                    'translation' => [
                        'id',
                        'language',
                        'title',
                        'description',
                    ],
                    'translations' => [
                        'id',
                        'language',
                        'title',
                        'description',
                    ],
                ],
            ]
        );

        $this->postGraphQlBackOfficeUpload($query->getUploadMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'translation' => [
                                'id',
                                'language',
                                'notice',
                                'description',
                            ],
                            'translations' => [
                                [
                                    'id',
                                    'language',
                                    'notice',
                                    'description',
                                ]
                            ],
                            'packages' => [
                                [
                                    'id',
                                    'image' => [
                                        'name'
                                    ],
                                    'translation' => [
                                        'id',
                                        'language',
                                        'title',
                                        'description',
                                    ],
                                    'translations' => [
                                        [
                                            'id',
                                            'language',
                                            'title',
                                            'description',
                                        ]
                                    ],
                                ]
                            ],
                        ],
                    ],
                ]
            )->json('data.' . self::MUTATION . '.id');
    }
}
