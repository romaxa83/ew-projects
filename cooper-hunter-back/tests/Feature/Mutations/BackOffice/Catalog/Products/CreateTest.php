<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Products;

use App\Enums\Catalog\Products\ProductUnitSubType;
use App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductCreateMutation;
use App\Models\Admins\Admin;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Products;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\LabelBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\ProductDtoTest;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    protected LabelBuilder $labelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->labelBuilder = resolve(LabelBuilder::class);
    }

    public const MUTATION = ProductCreateMutation::NAME;

    /** @test */
    public function success(): void
    {
        Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $relationProduct1 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $relationProduct2 = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create();

        $videoLinks = VideoLink::factory()->count(4)->create();

        $category = Category::factory()->create();
        $relativeCategory = Category::factory()->create();

        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $data = ProductDtoTest::data();
        $data['active'] = true;
        $data['category_id'] = $category->id;
        $data['video_link_ids'] = [
            $videoLinks[1]->id,
            $videoLinks[2]->id
        ];
        $data['relations'] = [
            $relationProduct1->id,
            $relationProduct2->id
        ];

        $data['relative_category_ids'] = [$relativeCategory->id];

        $this->postGraphQLBackOffice(
            $this->getQueryStr($data)
        )
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'id',
                        'active',
                        'category' => [
                            'id'
                        ],
                        'certificates',
                        'video_links' => [
                            '*' => [
                                'id',
                                'link'
                            ]
                        ],
                        'relations' => [
                            [
                                'id'
                            ]
                        ],
                        'translation' => [
                            'id',
                            'language',
                            'description',
                            'seo_title',
                            'seo_description',
                            'seo_h1',
                        ],
                        'translations' => [
                            '*' => [
                                'id',
                                'language',
                                'description',
                                'seo_title',
                                'seo_description',
                                'seo_h1',
                            ]
                        ],
                        'unit_sub_type'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'active' => true,
                        'category' => [
                            'id' => $category->id
                        ],
                        'certificates' => [],
                        'video_links' => [
                            [
                                'id' => $videoLinks[1]->id,
                                'link' => $videoLinks[1]->link
                            ],
                            [
                                'id' => $videoLinks[2]->id,
                                'link' => $videoLinks[2]->link
                            ],
                        ],
                        'relations' => [
                            [
                                'id' => $relationProduct2->id,
                            ],
                            [
                                'id' => $relationProduct1->id
                            ]
                        ],
                        'unit_sub_type' => null
                    ]
                ]
            ]);
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }

    private function getQueryStr(array $data): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args([
                'product' => [
                    'active' => $data['active'],
                    'category_id' => $data['category_id'],
                    'relative_category_ids' => $data['relative_category_ids'] ?? [],
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'video_link_ids' => $data['video_link_ids'],
                    'relations' => $data['relations'],
                    'translations' => [
                        [
                            'language' => new EnumValue($data['translations']['es']['language']),
                            'seo_title' => 'Seo title es',
                            'seo_description' => 'Seo description es',
                            'seo_h1' => 'Seo h1 es',
                        ],
                        [
                            'language' => new EnumValue($data['translations']['en']['language']),
                            'seo_title' => 'Seo title en',
                            'seo_description' => 'Seo description en',
                            'seo_h1' => 'Seo h1 en',
                        ]
                    ],
                    'unit_sub_type' => data_get($data, 'unit_sub_type')
                ]
            ])
            ->select([
                'id',
                'active',
                'category' => [
                    'id'
                ],
                'relative_categories' => [
                    'id',
                    'slug',
                ],
                'certificates' => [
                    'id',
                ],
                'video_links' => [
                    'id',
                    'link'
                ],
                'relations' => [
                    'id'
                ],
                'translation' => [
                    'id',
                    'language',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ],
                'translations' => [
                    'id',
                    'language',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ],
                'unit_sub_type'
            ])
            ->make();
    }

    /** @test */
    public function success_with_features(): void
    {
        $feature1 = Feature::factory()
            ->has(Value::factory())
            ->create();

        $feature2 = Feature::factory()
            ->has(Value::factory())
            ->create();

        $category = Category::factory()->create();

        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $data = ProductDtoTest::data();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'product' => [
                            'category_id' => $category->id,
                            'title' => $data['title'],
                            'slug' => $data['slug'],
                            'features' => [
                                [
                                    'value_id' => $feature1->values[0]->id,
                                ],
                                [
                                    'value_id' => $feature2->values[0]->id,
                                ]
                            ],
                            'translations' => [
                                [
                                    'language' => new EnumValue($data['translations']['es']['language']),
                                    'seo_title' => 'Seo title es',
                                    'seo_description' => 'Seo description es',
                                    'seo_h1' => 'Seo h1 es',
                                ],
                                [
                                    'language' => new EnumValue($data['translations']['en']['language']),
                                    'seo_title' => 'Seo title en',
                                    'seo_description' => 'Seo description en',
                                    'seo_h1' => 'Seo h1 en',
                                ],
                            ],
                            'unit_sub_type' => ProductUnitSubType::MULTI()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'values' => [
                            'id',
                            'feature' => [
                                'id'
                            ],
                            'metric' => [
                                'id'
                            ]
                        ],
                        'unit_sub_type'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'unit_sub_type' => ProductUnitSubType::MULTI
                    ]
                ]
            ])
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'active',
                            'values' => [
                                '*' => [
                                    'id',
                                    'feature' => [
                                        'id'
                                    ],
                                    'metric'
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function success_with_certificates(): void
    {
        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $data = ProductDtoTest::data();

        $res = $this->postGraphQLBackOffice($this->getQueryStrWithCerts($data));
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('certificates', $resData);
        $this->assertArrayHasKey('id', Arr::get($resData, 'certificates.0'));
    }

    private function getQueryStrWithCerts(array $data): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args([
                'product' => [
                    'category_id' => $data['category_id'],
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'certificates' => [
                        [
                            'type_name' => 'type1',
                            'number' => 'number1',
                            'link' => 'https://example.com/1',
                        ],
                        [
                            'type_name' => 'type1',
                            'number' => 'number2',
                            'link' => 'https://example.com/1',
                        ],
                        [
                            'type_name' => 'type2',
                            'number' => 'number3',
                        ]
                    ],
                    'translations' => [
                        [
                            'language' => new EnumValue($data['translations']['es']['language']),
                            'seo_title' => 'Seo title es',
                            'seo_description' => 'Seo description es',
                            'seo_h1' => 'Seo h1 es',
                        ],
                        [
                            'language' => new EnumValue($data['translations']['en']['language']),
                            'seo_title' => 'Seo title en',
                            'seo_description' => 'Seo description en',
                            'seo_h1' => 'Seo h1 en',
                        ],
                    ]
                ]
            ])
            ->select([
                'id',
                'active',
                'certificates' => [
                    'id'
                ]
            ])
            ->make();
    }

    /** @test */
    public function success_without_not_required_fields(): void
    {
        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $data = ProductDtoTest::data();

        unset(
            $data['video_link_ids'],
            $data['troubleshoot_ids'],
            $data['relations'],
            $data['features'],
            $data['active'],
            $data['sort']
        );

        $this->postGraphQLBackOffice($this->getQueryStrWithoutRequiredFields($data))
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'id',
                        'active',
                        'category' => [
                            'id'
                        ],
                        'video_links',
                        'values',
                        'relations',
                        'translation' => [
                            'id',
                            'language',
                            'description',
                            'seo_title',
                            'seo_description',
                            'seo_h1',
                        ],
                        'translations' => [
                            '*' => [
                                'id',
                                'language',
                                'description',
                                'seo_title',
                                'seo_description',
                                'seo_h1',
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'active' => true,
                        'category' => [
                            'id' => $data['category_id']
                        ],
                        'video_links' => [],
                        'values' => [],
                        'relations' => [],
                    ]
                ]
            ]);
    }

    private function getQueryStrWithoutRequiredFields(array $data): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args([
                'product' => [
                    'category_id' => $data['category_id'],
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'certificate_ids' => $data['certificate_ids'],
                    'translations' => [
                        [
                            'language' => new EnumValue($data['translations']['es']['language']),
                            'seo_title' => 'Seo title es',
                            'seo_description' => 'Seo description es',
                            'seo_h1' => 'Seo h1 es',
                        ],
                        [
                            'language' => new EnumValue($data['translations']['en']['language']),
                            'seo_title' => 'Seo title en',
                            'seo_description' => 'Seo description en',
                            'seo_h1' => 'Seo h1 en',
                        ],
                    ]
                ]
            ])
            ->select([
                'id',
                'active',
                'category' => [
                    'id'
                ],
                'video_links' => [
                    'id',
                    'link'
                ],
                'values' => [
                    'id',
                    'feature' => [
                        'id'
                    ]
                ],
                'relations' => [
                    'id'
                ],
                'translation' => [
                    'id',
                    'language',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ],
                'translations' => [
                    'id',
                    'language',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ]
            ])
            ->make();
    }

    /** @test */
    public function fail_without_category(): void
    {
        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $data = ProductDtoTest::data();
        $data['active'] = 'true';
        unset($data['category_id']);

        $this->postGraphQLBackOffice($this->getQueryStrWithoutCategory($data))
            ->assertJson([
                'errors' => [
                    [
                        'extensions' => [
                            'category' => 'graphql'
                        ]
                    ]
                ]
            ]);
    }

    private function getQueryStrWithoutCategory(array $data): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args([
                'product' => [
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'certificate_ids' => $data['certificate_ids'],
                    'translations' => [
                        [
                            'language' => new EnumValue($data['translations']['es']['language']),
                            'seo_title' => 'Seo title es',
                            'seo_description' => 'Seo description es',
                            'seo_h1' => 'Seo h1 es',
                        ],
                        [
                            'language' => new EnumValue($data['translations']['en']['language']),
                            'seo_title' => 'Seo title en',
                            'seo_description' => 'Seo description en',
                            'seo_h1' => 'Seo h1 en',
                        ],
                    ]
                ]
            ])
            ->select([
                'id',
                'active',
                'category' => [
                    'id'
                ]
            ])
            ->make();
    }

    /** @test */
    public function fail_wrong_video_links(): void
    {
        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $data = ProductDtoTest::data();
        $data['video_link_ids'] = [1, 3];

        $res = $this->postGraphQLBackOffice($this->getQueryStr($data));

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function fail_wrong_unit_sub_type(): void
    {
        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $data = ProductDtoTest::data();
        $data['unit_sub_type'] = 'wrong';

        $res = $this->postGraphQLBackOffice($this->getQueryStr($data))
        ;

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('Field "productCreate" argument "product" requires type ProductUnitSubTypeTypeEnumType, found "wrong".', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Products\UpdatePermission::KEY]);

        $data = ProductDtoTest::data();
        $data['active'] = true;

        $res = $this->postGraphQLBackOffice($this->getQueryStrWithoutRequiredFields($data));

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    public function test_create_with_manuals(): void
    {
        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'product' => [
                    'category_id' => Category::factory()->create()->id,
                    'title' => 'some title',
                    'slug' => 'some slug',
                    'manual_ids' => Manual::factory()->times(3)->create()->pluck('id')->toArray(),
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
                'manuals' => [
                    'id',
                ],
            ],
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'manuals' => [
                                [
                                    'id',
                                ]
                            ],
                        ],
                    ],
                ],
            );
    }

    public function test_create_with_labels(): void
    {
        $this->loginByAdminManager([Products\CreatePermission::KEY]);

        $label_1 = $this->labelBuilder->create();
        $label_2 = $this->labelBuilder->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'product' => [
                    'category_id' => Category::factory()->create()->id,
                    'title' => 'some title',
                    'slug' => 'some slug',
                    'label_ids' => [
                        $label_1->id,
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
                            ['id'=> $label_2->id],
                            ['id'=> $label_1->id],
                        ],
                    ],
                ],
            ])
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'labels' => [
                                ['id']
                            ],
                        ],
                    ],
                ],
            );
    }
}
