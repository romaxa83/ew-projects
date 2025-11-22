<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\BackOffice\Catalog\Categories\CategoryQuery;
use App\Models\Catalog\Categories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class CategoryImageTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const QUERY = CategoryQuery::NAME;

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function test_get_category_image(): void
    {
        $this->loginAsSuperAdmin();

        $this->fakeMediaStorage();

        $category = Category::factory()->create();
        $category->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Category::MEDIA_COLLECTION_NAME);

        $query = sprintf(
            'query {
                %s (
                    id: %d
                ) {
                    image {
                        id
                        url
                        name
                        file_name
                        size
                        conventions {
                            convention
                            url
                        }
                    }
                }
            }',
            self::QUERY,
            $category->id
        );

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'image' => [
                                'id',
                                'url',
                                'name',
                                'file_name',
                                'size',
                                'conventions' => [
                                    [
                                        'convention',
                                        'url',
                                    ]
                                ],
                            ],
                        ],
                    ],
                ]
            );
    }
}
