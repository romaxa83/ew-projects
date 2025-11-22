<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Categories;

use App\GraphQL\Queries\FrontOffice\Catalog\Categories\CategoryQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class CategoryQueryTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const QUERY = CategoryQuery::NAME;

    public function test_unpublished_category_error_fix(): void
    {
        $category = Category::factory()
            ->disabled()
            ->create();

        $this->postGraphQL(
            GraphQLQuery::query(self::QUERY)
                ->args(
                    [
                        'slug' => $category->slug,
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => null,
                    ]
                ]
            );
    }

    /**
     * @throws FileNotFoundException
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function test_guest_can_view_category(): void
    {
        $this->assertViewCategory();
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws FileNotFoundException
     */
    protected function assertViewCategory(): void
    {
        $this->fakeMediaStorage();

        $category = Category::factory()->create();

        $category->addMedia($this->getSampleImage())
            ->toMediaCollection($category->getMediaCollectionName());

        Product::factory()
            ->times(3)
            ->for($category)
            ->create();

        $query = sprintf(
            'query {
                %s (
                    id: %s
                ) {
                    id
                    products_count
                    image {
                        id
                        name
                        conventions {
                            convention
                            url
                        }
                    }
                }
            }
            ',
            self::QUERY,
            $category->id
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $category->id,
                            'products_count' => trans_choice('messages.products_count', $count = 3, compact('count')),
                        ],
                    ],
                ]
            );
    }

    /**
     * @throws FileNotFoundException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_user_can_view_category(): void
    {
        $this->loginAsUserWithRole();

        $this->assertViewCategory();
    }

    /**
     * @throws FileNotFoundException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_technician_can_view_category(): void
    {
        $this->loginAsTechnicianWithRole();

        $this->assertViewCategory();
    }

    public function test_get_parents_tree(): void
    {
        Category::query()->delete();

        Category::factory()
            ->withParent(
                Category::factory()
                    ->withParent(
                        Category::factory()
                            ->withParent(
                                Category::factory()
                                    ->withParent(
                                        Category::factory()
                                            ->withParent(
                                                Category::factory()
                                                    ->withParent(
                                                        Category::factory()
                                                            ->withParent(
                                                                Category::factory()
                                                                    ->withParent(
                                                                        Category::factory()
                                                                            ->withParent(
                                                                                Category::factory()
                                                                            )
                                                                    )
                                                            )
                                                    )
                                            )
                                    )
                            )
                    )
            )
            ->create();

        $category = Category::query()->latest('sort')->first();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'id' => $category->id
                ]
            )
            ->select(
                [
                    'id',
                    'children' => [
                        'id',
                    ],
                    'parent' => [
                        'id',
                        'children' => [
                            'id',
                        ],
                        'parent' => [
                            'id',
                            'children' => [
                                'id',
                            ],
                            'parent' => [
                                'id',
                                'children' => [
                                    'id',
                                ],
                                'parent' => [
                                    'id',
                                    'children' => [
                                        'id',
                                    ],
                                    'parent' => [
                                        'id',
                                        'children' => [
                                            'id',
                                        ],
                                        'parent' => [
                                            'id',
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure();
    }
}
