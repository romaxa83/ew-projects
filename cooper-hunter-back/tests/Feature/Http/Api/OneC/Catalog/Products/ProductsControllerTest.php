<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Products;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Models\Catalog\Brands\Brand;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductTranslation;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\OneC\Moderator;
use App\Permissions\Catalog\Categories\DeletePermission;
use Database\Factories\Catalog\Brands\BrandFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class ProductsControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    protected $productBuilder;

    public function test_unauthorized(): void
    {
        $this->getJson(route('1c.products.index'))
            ->assertUnauthorized();
    }

    public function test_no_permission(): void
    {
        $role = $this->generateRole(
            'Wrong permission role',
            [DeletePermission::KEY],
            Moderator::GUARD
        );

        $this->loginAsModerator(role: $role);

        $this->getJson(route('1c.products.index'))
            ->assertForbidden();
    }

    public function test_index(): void
    {
        $this->loginAsModerator();

        Product::factory()
            ->times(10)
            ->has(
                ProductTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->getJson(route('1c.products.index'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'guid',
                            'title',
                            'translations' => [
                                [
                                    'description',
                                    'language',
                                    'seo_title',
                                    'seo_description',
                                    'seo_h1',
                                ],
                            ],
                        ],
                    ],
                ],
            );
    }

    public function test_show(): void
    {
        $this->loginAsModerator();

        $product = Product::factory()
            ->has(
                ProductTranslation::factory()->allLocales(),
                'translations'
            )
            ->has(Certificate::factory())
            ->create();

        $this->getJson(route('1c.products.show', $product->guid))
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                'id',
                'guid',
                'translations' => [
                    [
                        'description',
                        'language',
                        'seo_title',
                        'seo_description',
                        'seo_h1',
                    ],
                ],
            ],
        ];
    }

    public function test_show_not_found(): void
    {
        $this->loginAsModerator();

        $this->getJson(route('1c.products.show', 0))
            ->assertNotFound();
    }

    public function test_update_incomplete_translations(): void
    {
        $this->loginAsModerator();

        $product = Product::factory()
            ->has(
                ProductTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->putJson(
            route('1c.products.update', $product->guid),
            [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'slug' => $product->slug,
                'title' => $product->title . '_test',
                'translations' => [
                    [
                        'language' => 'en',
                        'seo_title' => 'seo_title',
                        'seo_description' => 'seo_description',
                        'seo_h1' => 'seo_h1',
                    ]
                ]
            ]
        )
            ->assertUnprocessable();
    }

    public function test_store(): void
    {
        $this->loginAsModerator();

        $brand_cooper = Brand::factory()->create(['slug' => ProductOwnerType::COOPER]);
        $brand_olmo = Brand::factory()->create(['slug' => ProductOwnerType::OLMO]);

        $f1 = Feature::factory()->create();
        $f2 = Feature::factory()->create();
        $f3 = Feature::factory()->create();

        $data = [
            'guid' => Uuid::uuid4(),
            'slug' => 'product-slug-123-123',
            'title' => 'product title',
            'seer' => 11.1,
            'category_guid' => Category::factory()->create()->guid,
            'features' => [
                [
                    'guid' => $f1->guid,
                    'values' => $v1 = [
                        'val 1',
                        'val 2',
                        'val 3',
                    ],
                ],
                [
                    'guid' => $f2->guid,
                    'values' => $v2 = [
                        'val 4',
                        'val 5',
                        'val 6',
                    ],
                ],
                [
                    'guid' => $f3->guid,
                    'values' => $v3 = [
                        'val 4',
                        'val 5',
                        'val 6',
                    ],
                ]
            ],
            'certificates' => [
                [
                    'type_name' => 'type1',
                    'number' => 'number1',
                    'link' => 'https://example.com/74',
                ]
            ],
            'translations' => [
                [
                    'language' => 'en',
                    'seo_title' => 'seo_title en',
                    'seo_description' => 'seo_description en',
                    'seo_h1' => 'seo_h1 en',
                ],
                [
                    'language' => 'es',
                    'seo_title' => 'seo_title es',
                    'seo_description' => 'seo_description es',
                    'seo_h1' => 'seo_h1 es',
                ]
            ]
        ];

        $id = $this->postJson(
            route('1c.products.store'),
            $data,
        )
            ->assertCreated()
            ->json('data.id')
        ;

        self::assertEquals($v1, $f1->values()->pluck('title')->toArray());
        self::assertEquals($v2, $f2->values()->pluck('title')->toArray());
        self::assertEquals($v3, $f3->values()->pluck('title')->toArray());

        $model = Product::find($id);
        self::assertEquals($model->owner_type, ProductOwnerType::COOPER);
        self::assertEquals($model->brand_id,  $brand_cooper->id);
    }

    public function test_update(): void
    {
        $this->loginAsModerator();

        $product = Product::factory()
            ->has(
                ProductTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->putJson(
            route('1c.products.update', $product->guid),
            [
                'category_guid' => $product->category->guid,
                'slug' => $product->slug,
                'title' => $product->title . '_test',
                'translations' => [
                    [
                        'language' => 'en',
                        'seo_title' => 'seo_title en',
                        'seo_description' => 'seo_description en',
                        'seo_h1' => 'seo_h1 en',
                    ],
                    [
                        'language' => 'es',
                        'seo_title' => 'seo_title es',
                        'seo_description' => 'seo_description es',
                        'seo_h1' => 'seo_h1 es',
                    ]
                ]
            ]
        )
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    public function test_update_patch(): void
    {
        $this->loginAsModerator();

        $product = Product::factory()
            ->hasAttached(
                factory: VideoLink::factory()
                    ->count(4),
                relationship: 'videoLinks'
            )
            ->hasAttached(
                factory: Manual::factory()
                    ->count(4),
                relationship: 'manuals'
            )
            ->create();

        self::assertCount(2, $product->translations);
        self::assertCount(4, $product->videoLinks);
        self::assertCount(4, $product->manuals);
        self::assertCount(0, $product->values);

        $this->patchJson(
            route('1c.products.update', $product->guid),
            [
                'category_guid' => $product->category->guid,
                'slug' => $product->slug,
                'title' => $product->title . '_test',
                'features' => [
                    [
                        'guid' => Feature::factory()->create()->guid,
                        'values' => [
                            'val 1',
                            'val 2',
                            'val 3',
                        ],
                    ],
                ]
            ]
        )
            ->assertOk();

        $product = $product->fresh();

        self::assertCount(2, $product->translations);
        self::assertCount(4, $product->videoLinks);
        self::assertCount(4, $product->manuals);
        self::assertCount(3, $product->values);

        $this->patchJson(
            route('1c.products.update', $product->guid),
            [
                'category_guid' => $product->category->guid,
                'slug' => $product->slug,
                'title' => $product->title . '_test',
                'features' => []
            ]
        )
            ->assertOk();

        $product = $product->fresh();

        self::assertCount(2, $product->translations);
        self::assertCount(4, $product->videoLinks);
        self::assertCount(4, $product->manuals);
        self::assertCount(0, $product->values);
    }

    public function test_delete(): void
    {
        $this->loginAsModerator();

        $product = Product::factory()->create();

        $this->assertDatabaseHas(Product::TABLE, ['guid' => $product->guid]);

        $this->deleteJson(route('1c.products.destroy', $product->guid))
            ->assertOk();

        $this->assertDatabaseMissing(Product::TABLE, ['guid' => $product->guid]);
    }

    public function test_update_guid(): void
    {
        $this->loginAsModerator();

        $category = Product::factory()->create(['guid' => null]);
        $guid = Uuid::uuid4();

        $this->assertDatabaseMissing(Product::TABLE, ['guid' => $guid]);

        $this->postJson(
            route('1c.products.update.guid'),
            [
                'data' => [
                    [
                        'id' => $category->id,
                        'guid' => $guid,
                    ]
                ]
            ]
        )->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'guid',
                    ]
                ],
            ]
        );

        $this->assertDatabaseHas(Product::TABLE, ['guid' => $guid]);
    }
}
