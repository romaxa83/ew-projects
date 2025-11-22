<?php

namespace Tests\Feature\Queries\Common\About;

use App\GraphQL\Queries\Common\About\BaseAboutCompanyQuery;
use App\Models\About\AboutCompany;
use App\Models\About\AboutCompanyTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class AboutCompanyQueryTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const QUERY = BaseAboutCompanyQuery::NAME;

    /**
     * @throws FileNotFoundException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_about_company(): void
    {
        $this->fakeMediaStorage();

        $about = AboutCompany::factory()
            ->has(AboutCompanyTranslation::factory()->allLocales(), 'translations')
            ->create();

        $about->addMedia($this->getSampleImage())->toMediaCollection(AboutCompany::MEDIA_COLLECTION_NAME);

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'images' => [
                    'name'
                ],
                'translations' => [
                    'video_link',
                    'title',
                    'description',
                ],
            ],
        );

        $jsonStructure = [
            'data' => [
                self::QUERY => [
                    'images' => [
                        [
                            'name'
                        ]
                    ],
                    'translations' => [
                        [
                            'video_link',
                            'title',
                            'description',
                        ]
                    ],
                ]
            ],
        ];

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonStructure($jsonStructure);

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonStructure($jsonStructure);
    }
}
