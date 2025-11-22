<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Products;

use App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductsQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Metric;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Videos\VideoLink;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class ProductsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const QUERY = ProductsQuery::NAME;

    public function test_get_product_list(): void
    {
        $this->fakeMediaStorage();
        $this->loginAsTechnicianWithRole();

        $category = Category::factory()
            ->has(
                CategoryTranslation::factory()
                    ->locale(),
                'translations'
            )
            ->create();

        $products = Product::factory()
            ->times(1)
            ->has(
                VideoLink::factory(),
                'videoLinks'
            )
            ->hasAttached(
                Certificate::factory()
            )
            ->hasAttached(
                Manual::factory()
                    ->for(
                        ManualGroup::factory()
                            ->has(
                                ManualGroupTranslation::factory()
                                    ->locale(),
                                'translations'
                            ),
                        'group'
                    )
            )
            ->hasAttached(
                Value::factory()
                    ->for(
                        Feature::factory()->state(['display_in_mobile' => true])
                    )
            )
            ->hasAttached(
                Value::factory()
                    ->times(3)
                    ->for(
                        Feature::factory()->state(['display_in_web' => true])
                    )
            )
            ->create(
                [
                    'category_id' => $category->id
                ]
            );

        $products->each(
        /**
         * @throws FileDoesNotExist
         * @throws FileIsTooBig
         */
            static fn(Product $product) => $product->addMedia(
                UploadedFile::fake()->image('image.png')
            )
                ->toMediaCollection(Product::MEDIA_COLLECTION_NAME)
        );

        $query = new GraphQLQuery(
            self::QUERY,
            select: [
                'data' => [
                    'id',
                    'active',
                    'category' => [
                        'id',
                        'translation' => [
                            'title',
                        ],
                    ],
                    'images' => [
                        'url',
                        'name',
                    ],
                    'video_links' => [
                        'id',
                        'group' => [
                            'id'
                        ],
                    ],
                    'values' => [
                        'id',
                        'feature' => [
                            'id',
                        ],
                    ],
                    'mobile_values' => [
                        'id',
                        'feature' => [
                            'id',
                        ],
                    ],
                    'web_values' => [
                        'id',
                        'feature' => [
                            'id',
                        ],
                    ],
                    'certificates' => [
                        'id',
                        'type_name',
                        'number',
                    ],
                    'manuals' => [
                        'pdf' => [
                            'url',
                        ],
                        'group' => [
                            'translation' => [
                                'title'
                            ],
                        ],
                    ],
                    'similar_products' => [
                        'id'
                    ]
                ],
            ],
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id',
                                    'active',
                                    'category' => [
                                        'id',
                                        'translation' => [
                                            'title'
                                        ],
                                    ],
                                    'images' => [
                                        [
                                            'name',
                                            'url',
                                        ]
                                    ],
                                    'video_links' => [
                                        [
                                            'id'
                                        ]
                                    ],
                                    'values' => [
                                        [
                                            'id',
                                            'feature' => [
                                                'id'
                                            ],
                                        ]
                                    ],
                                    'mobile_values' => [
                                        [
                                            'id',
                                            'feature' => [
                                                'id'
                                            ],
                                        ]
                                    ],
                                    'web_values' => [
                                        [
                                            'id',
                                            'feature' => [
                                                'id'
                                            ],
                                        ]
                                    ],
                                    'certificates' => [
                                        [
                                            'id',
                                            'type_name',
                                            'number',
                                        ]
                                    ],
                                    'manuals' => [
                                        [
                                            'pdf' => [
                                                'url',
                                            ],
                                            'group' => [
                                                'translation' => [
                                                    'title'
                                                ],
                                            ],
                                        ]
                                    ],
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }

    public function test_get_products_from_all_nested_categories(): void
    {
        $root = Category::factory()->create();

        $child1 = Category::factory()
            ->for($root, 'parent')
            ->create();

        $child2 = Category::factory()
            ->for($root, 'parent')
            ->create();

        Product::factory()
            ->times(5)
            ->for($child1)
            ->create();

        Product::factory()
            ->times(5)
            ->for($child2)
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                ['category_id' => $root->id]
            )->select(
                [
                    'data' => [
                        'id'
                    ],
                ]
            )->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(10, 'data.' . self::QUERY . '.data');
    }

    public function test_filter_products_by_ids(): void
    {
        $category = Category::factory()
            ->create();

        $products = Product::factory()
            ->times(10)
            ->hasAttached(
                Value::factory()
                    ->times(3)
                    ->for(
                        Feature::factory()
                    )
            )
            ->create(
                [
                    'category_id' => $category->id
                ]
            );

        $this->loginAsTechnicianWithRole();

        $this->postGraphQL(
            GraphQLQuery::query(ProductsQuery::NAME)
                ->args(
                    [
                        'ids' => [
                            $products[0]->id,
                            $products[2]->id,
                            $products[7]->id,
                        ],
                        'sort' => 'sort-desc'
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id'
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id' => $products[7]->id,
                                ],
                                [
                                    'id' => $products[2]->id,
                                ],
                                [
                                    'id' => $products[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.' . ProductsQuery::NAME . '.data');
    }

    public function test_get_similar_products(): void
    {
        $values = Value::factory()
            ->for(
                Feature::factory()
                    ->web()
                    ->create()
            )
            ->has(
                Metric::factory()
            )
            ->count(3)
            ->create();

        $products = Product::factory(
            [
                'category_id' => Category::factory()
                    ->create()->id
            ]
        )
            ->hasAttached(
                $values
            )
            ->count(3)
            ->create();

        $this->loginAsTechnicianWithRole();

        $this->postGraphQL(
            GraphQLQuery::query(ProductsQuery::NAME)
                ->args(
                    [
                        'ids' => [
                            $products[1]->id
                        ]
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'similar_products' => [
                                'id'
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'similar_products' => [
                                        [
                                            'id' => $products[0]->id,
                                        ],
                                        [
                                            'id' => $products[2]->id,
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . ProductsQuery::NAME . '.data.0.similar_products');
    }

    public function test_get_products_from_relative_categories(): void
    {
        $category = Category::factory()->create();

        Product::factory()
            ->times(4)
            ->for($category)
            ->create();

        Product::factory()
            ->hasAttached($category, relationship: 'relativeCategories')
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'category_slug' => $category->slug
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ]
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonCount(5, 'data.' . self::QUERY . '.data');
    }

    public function test_filters_products_while_logged_in(): void
    {
        /**
         * Issue with 'SELECT list is not in GROUP BY'
         */
        $this->loginAsTechnicianWithRole();

        $this->test_filter_products_by_values();
    }

    public function test_filter_products_by_values(): void
    {
        $category = Category::factory()->create();

        $products = Product::factory()
            ->times(10)
            ->hasAttached(
                Value::factory()
                    ->times(3)
                    ->for(
                        Feature::factory()
                    )
            )
            ->create(
                [
                    'category_id' => $category->id
                ]
            );

        $singleProductValues = $products->first()->values->pluck('id')->toArray();

        $query = $this->makeFilterQuery(
            [
                'category_id' => $category->id,
                'value_ids' => $singleProductValues
            ]
        );

        $this->postGraphQL($query)
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data');

        $query = $this->makeFilterQuery(
            [
                'category_id' => $category->id,
            ]
        );

        $this->postGraphQL($query)
            ->assertJsonCount(10, 'data.' . self::QUERY . '.data');
    }

    protected function makeFilterQuery(array $args): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select(
                [
                    'data' => [
                        'id',
                        'is_favourite'
                    ],
                ]
            )
            ->make();
    }
}
