<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Products;

use App\Enums\Catalog\Products\ProductUnitSubType;
use App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductUpdateMutation;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Troubleshoots;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Products;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\LabelBuilder;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Unit\Dto\Catalog\ProductDtoTest;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    private Product $product;
    protected LabelBuilder $labelBuilder;
    protected ProductBuilder $productBuilder;

    public const MUTATION = ProductUpdateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();
        $this->labelBuilder = resolve(LabelBuilder::class);
        $this->productBuilder = app(ProductBuilder::class);

        $this->product = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->has(Category::factory(), 'category')
            ->hasAttached(
                factory:
                VideoLink::factory()
                    ->count(4)
                    ->create(),
                relationship: 'videoLinks'
            )
            ->hasAttached(
                factory:
                Manual::factory()
                    ->count(4)
                    ->create(),
                relationship: 'manuals'
            )
            ->hasAttached(
                factory:
                Product::factory()
                    ->count(2)
                    ->create(),
                relationship: 'relationProducts'
            )
            ->hasAttached(
                factory: Troubleshoots\Group::factory()
                ->has(
                    Troubleshoots\Troubleshoot::factory()
                        ->count(3),
                )
                ->count(2)
                ->create(),
                relationship: 'troubleshootGroups'
            )
            ->create();
        $this->loginByAdminManager([Products\UpdatePermission::KEY]);
    }

    /** @test */
    public function success(): void
    {
        $productData = ProductDtoTest::data();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProductUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $this->product->id,
                        'product' => [
                            'active' => false,
                            'category_id' => $productData['category_id'],
                            'title' => $productData['title'],
                            'slug' => $productData['slug'],
                            'translations' => array_values($productData['translations']),
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'category' => [
                            'id'
                        ],
                        'title',
                        'slug',
                        'troubleshoot_groups' => [
                            'id',
                            'troubleshoots' => [
                                'id',
                                'name',
                            ]
                        ],
                        'relations' => [
                            'id'
                        ],
                        'unit_sub_type',
                        'translations' => [
                            'seo_title',
                            'seo_description',
                            'seo_h1',
                        ]
                    ]
                )
                ->make()
        )
            ->assertJson([
                'data' => [
                    ProductUpdateMutation::NAME => [
                        'id' => $this->product->id,
                        'active' => false,
                        'category' => [
                            'id' => $productData['category_id']
                        ],
                        'title' => $productData['title'],
                        'slug' => $productData['slug'],
                        'troubleshoot_groups' => [],
                        'relations' => [],
                        'unit_sub_type' => null,
                        'translations' => [
                            [
                                'seo_title' => $productData['translations']['en']['seo_title']
                            ],
                            [
                                'seo_title' => $productData['translations']['es']['seo_title']
                            ]
                        ],
                    ]
                ]
            ]);
    }

    /** @test */
    public function success_update_video_links(): void
    {
        $productData = ProductDtoTest::data();
        $videoLink = VideoLink::factory()
            ->create();
        $relation = Product::factory()
            ->create();
        $feature1 = Feature::factory()
            ->has(Value::factory())
            ->create();
        $feature2 = Feature::factory()
            ->has(Value::factory())
            ->create();
        $manuals = Manual::factory()
            ->times(3)
            ->create();

        $troubleshootGroup = Troubleshoots\Group::factory()
            ->has(
                Troubleshoots\Troubleshoot::factory()
                    ->count(3)
            )
            ->count(2)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ProductUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $this->product->id,
                        'product' => [
                            'active' => false,
                            'category_id' => $this->product->category_id,
                            'title' => $productData['title'],
                            'slug' => $productData['slug'],
                            'translations' => array_values($productData['translations']),
                            'video_link_ids' => [
                                $videoLink->id
                            ],
                            'relations' => [
                                $relation->id
                            ],
                            'features' => [
                                [
                                    'value_id' => $feature1->values[0]->id
                                ],
                                [
                                    'value_id' => $feature2->values[0]->id
                                ]
                            ],
                            'manual_ids' => $manuals->pluck('id')
                                ->toArray(),
                            'troubleshoot_group_ids' => $troubleshootGroup->pluck('id')
                                ->toArray(),
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'video_links' => [
                            'id',
                            'link'
                        ],
                        'relations' => [
                            'id'
                        ],
                        'values' => [
                            'id'
                        ],
                        'manuals' => [
                            'id'
                        ],
                        'troubleshoot_groups' => [
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
                        ProductUpdateMutation::NAME => [
                            'id' => $this->product->id,
                            'video_links' => [
                                [
                                    'id' => $videoLink->id,
                                    'link' => $videoLink->link,
                                ]
                            ],
                            'relations' => [
                                [
                                    'id' => $relation->id,
                                ]
                            ],
                            'values' => [
                                [
                                    'id' => $feature1->values[0]->id
                                ],
                                [
                                    'id' => $feature2->values[0]->id
                                ],
                            ],
                            'manuals' => $manuals->map(
                                fn(Manual $manual) => [
                                    'id' => $manual->id
                                ]
                            )
                                ->toArray(),
                            'troubleshoot_groups' => $troubleshootGroup->map(
                                fn (Troubleshoots\Group $group) => ['id' => $group->id]
                            )->toArray()
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function update_labels(): void
    {
        $label_1 = $this->labelBuilder->create();
        $label_2 = $this->labelBuilder->create();
        $label_3 = $this->labelBuilder->create();

        /** @var $product Product */
        $product = $this->productBuilder->setLabels($label_1)->create();

        $this->assertCount(1, $product->labels);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $product->id,
                'product' => [
                    'category_id' => Category::factory()->create()->id,
                    'title' => 'some title',
                    'slug' => 'some slug',
                    'label_ids' => [
                        $label_3->id,
                        $label_2->id,
                    ],
                    'translations' => [
                        [
                            'language' => new EnumValue('es'),
                            'description' => 'some desc es',
                            'seo_title' => 'Seo title es',
                            'seo_description' => 'Seo description es',
                            'seo_h1' => 'Seo h1 es',
                        ],
                        [
                            'language' => new EnumValue('en'),
                            'description' => 'some desc en',
                            'seo_title' => 'Seo title en',
                            'seo_description' => 'Seo description en',
                            'seo_h1' => 'Seo h1 en',
                        ]
                    ]
                ]
            ],
            [
                'id',
                'labels' => [
                    'id',
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'labels' => [
                            ['id'=> $label_3->id],
                            ['id'=> $label_2->id],
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.labels')
        ;
    }

    /** @test */
    public function update_unit_sub_type(): void
    {
        /** @var $product Product */
        $product = $this->productBuilder->create();

        $this->assertNull($product->unit_sub_type);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $product->id,
                'product' => [
                    'category_id' => Category::factory()->create()->id,
                    'title' => 'some title',
                    'slug' => 'some slug',
                    'unit_sub_type' => ProductUnitSubType::SINGLE(),
                    'translations' => [
                        [
                            'language' => new EnumValue('es'),
                            'description' => 'some desc es',
                            'seo_title' => 'Seo title es',
                            'seo_description' => 'Seo description es',
                            'seo_h1' => 'Seo h1 es',
                        ],
                        [
                            'language' => new EnumValue('en'),
                            'description' => 'some desc en',
                            'seo_title' => 'Seo title en',
                            'seo_description' => 'Seo description en',
                            'seo_h1' => 'Seo h1 en',
                        ]
                    ]
                ]
            ],
            [
                'id',
                'unit_sub_type',
            ],
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'unit_sub_type' => ProductUnitSubType::SINGLE,
                    ],
                ],
            ])
        ;

        $product->refresh();

        $this->assertTrue($product->unit_sub_type->isSingle());
    }
}

