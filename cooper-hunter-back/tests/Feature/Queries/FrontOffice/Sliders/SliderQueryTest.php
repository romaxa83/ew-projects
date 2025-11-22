<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\FrontOffice\Sliders;

use App\GraphQL\Queries\FrontOffice\Sliders\SliderQuery;
use App\Models\Sliders\Slider;
use App\Models\Sliders\SliderTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class SliderQueryTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const QUERY = SliderQuery::NAME;

    /**
     * @throws FileNotFoundException
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function test_get_list_success(): void
    {
        $this->fakeMediaStorage();

        $slider = Slider::factory()
            ->has(SliderTranslation::factory()->allLocales(), 'translations')
            ->create();

        $slider->addMedia(
            $this->getSampleImage()
        )->toMediaCollection($slider::MEDIA_COLLECTION_NAME);

        $this->query(
            select: $this->getSelect()
        )
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function query(array $args = [], array $select = []): TestResponse
    {
        $query = GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select($select ?: ['id']);

        return $this->postGraphQL($query->make());
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
            'media' => [
                'name',
                'url',
                'url_webp',
                'conventions' => [
                    'url',
                    'url_webp',
                ],
            ],
            'translation' => [
                'title',
                'description',
            ],
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                self::QUERY => [
                    array_merge(
                        $this->getSelect(),
                        [
                            'media' => [
                                'name',
                                'url',
                                'url_webp',
                                'conventions' => [
                                    [
                                        'url',
                                        'url_webp',
                                    ]
                                ],
                            ],
                        ]
                    )
                ],
            ],
        ];
    }
}
