<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Crud;

use App\Enums\Inventories\InventoryPackageType;
use App\Events\Events\Inventories\Inventories\UpdateInventoryEvent;
use App\Events\Listeners\Inventories\Inventories\SyncEComUpdateInventoryListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Jobs\Inventories\InventorySyncJob;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\InventoryFeatureValueBuilder;
use Tests\Builders\Inventories\UnitBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected CategoryBuilder $categoryBuilder;
    protected UnitBuilder $unitBuilder;
    protected SupplierBuilder $supplierBuilder;
    protected BrandBuilder $brandBuilder;
    protected SeoBuilder $seoBuilder;
    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;
    protected InventoryFeatureValueBuilder $inventoryFeatureValueBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->brandBuilder = resolve(BrandBuilder::class);
        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->unitBuilder = resolve(UnitBuilder::class);
        $this->supplierBuilder = resolve(SupplierBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
        $this->inventoryFeatureValueBuilder = resolve(InventoryFeatureValueBuilder::class);

        $unit = $this->unitBuilder->create();

        $this->data = [
            'name' => 'Product',
            'slug' => 'product',
            'stock_number' => 'product_stock_number',
            'article_number' => 'product_article_number',
            'unit_id' => $unit->id,
            'price_retail' => 10.5,
            'min_limit' => 4,
            'notes' => 'some text',
            'for_shop' => false,
            'length' => 10,
            'width' => 2.9,
            'height' => 4.0,
            'weight' => 5,
            'package_type' => InventoryPackageType::Custom->value,
            'min_limit_price' => 9,
            'is_new' => true,
            'is_popular' => true,
            'is_sale' => true,
            'discount' => 9,
            'old_price' => 9.10,
            'delivery_cost' => 4.10,
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $category Category */
        $category = $this->categoryBuilder->create();
        /** @var $brand Brand */
        $brand = $this->brandBuilder->create();
        /** @var $supplier Supplier */
        $supplier = $this->supplierBuilder->create();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data = $this->data;
        $data['category_id'] = $category->id;
        $data['brand_id'] = $brand->id;
        $data['supplier_id'] = $supplier->id;

        $this->assertNotEquals($model->name, data_get($data, 'name'));
        $this->assertNotEquals($model->slug, data_get($data, 'slug'));
        $this->assertNotEquals($model->stock_number, data_get($data, 'stock_number'));
        $this->assertNotEquals($model->article_number, data_get($data, 'article_number'));
        $this->assertNotEquals($model->unit_id, data_get($data, 'unit_id'));
        $this->assertNotEquals($model->category_id, data_get($data, 'category_id'));
        $this->assertNotEquals($model->brand_id, data_get($data, 'brand_id'));
        $this->assertNotEquals($model->supplier_id, data_get($data, 'supplier_id'));
        $this->assertNotEquals($model->price_retail, data_get($data, 'price_retail'));
        $this->assertNotEquals($model->min_limit, data_get($data, 'min_limit'));
        $this->assertNotEquals($model->notes, data_get($data, 'notes'));
        $this->assertNotEquals($model->for_shop, data_get($data, 'for_shop'));
        $this->assertNotEquals($model->length, data_get($data, 'length'));
        $this->assertNotEquals($model->width, data_get($data, 'width'));
        $this->assertNotEquals($model->height, data_get($data, 'height'));
        $this->assertNotEquals($model->weight, data_get($data, 'weight'));
        $this->assertNotEquals($model->package_type->value, data_get($data, 'package_type'));
        $this->assertNotEquals($model->min_limit_price, data_get($data, 'min_limit_price'));
        $this->assertNotEquals($model->is_new, data_get($data, 'is_new'));
        $this->assertNotEquals($model->is_popular, data_get($data, 'is_popular'));
        $this->assertNotEquals($model->is_sale, data_get($data, 'is_sale'));
        $this->assertNotEquals($model->discount, data_get($data, 'discount'));
        $this->assertNotEquals($model->old_price, data_get($data, 'old_price'));
        $this->assertNotEquals($model->delivery_cost, data_get($data, 'delivery_cost'));

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'stock_number' => data_get($data, 'stock_number'),
                    'article_number' => data_get($data, 'article_number'),
                    'unit_id' => data_get($data, 'unit_id'),
                    'category_id' => data_get($data, 'category_id'),
                    'supplier_id' => data_get($data, 'supplier_id'),
                    'brand' => [
                        'id' => data_get($data, 'brand_id')
                    ],
                    'price_retail' => data_get($data, 'price_retail'),
                    'min_limit' => data_get($data, 'min_limit'),
                    'notes' => data_get($data, 'notes'),
                    'for_shop' => data_get($data, 'for_shop'),
                    'length' => data_get($data, 'length'),
                    'width' => data_get($data, 'width'),
                    'height' => data_get($data, 'height'),
                    'weight' => data_get($data, 'weight'),
                    'package_type' => data_get($data, 'package_type'),
                    'min_limit_price' => data_get($data, 'min_limit_price'),
                    'is_new' => data_get($data, 'is_new'),
                    'is_popular' => data_get($data, 'is_popular'),
                    'is_sale' => data_get($data, 'is_sale'),
                    'discount' => data_get($data, 'discount'),
                    'old_price' => data_get($data, 'old_price'),
                    'delivery_cost' => data_get($data, 'delivery_cost'),
                    'main_image' => null,
                    'gallery' => [],
                    'seo' => [
                        'h1' => null,
                        'title' => null,
                        'keywords' => null,
                        'desc' => null,
                        'text' => null,
                        'image' => null,
                    ]
                ],
            ])
            ->assertJsonCount(0, 'data.gallery')
            ->assertJsonCount(0, 'data.features')
        ;
    }

    /** @test */
    public function success_update_check_event_send_to_ecomm()
    {
        Queue::fake();

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->for_shop(true)->create();

        $data = $this->data;
        $data['for_shop'] = true;

        $this->assertTrue($model->for_shop);

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                ],
            ])
        ;
//        Queue::assertPushed(InventorySyncJob::class, 1);
    }

    /** @test */
    public function success_update_check_event_send_to_ecomm_if_was_false()
    {
        Event::fake([UpdateInventoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->for_shop(false)->create();

        $data = $this->data;
        $data['for_shop'] = true;

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                ],
            ])
        ;

        Event::assertDispatched(fn (UpdateInventoryEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            UpdateInventoryEvent::class,
            SyncEComUpdateInventoryListener::class
        );
    }

    /** @test */
    public function success_update_check_event_send_to_ecomm_if_toggle_to_false()
    {
        Event::fake([UpdateInventoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->for_shop(true)->create();

        $data = $this->data;
        $data['for_shop'] = false;

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                ],
            ])
        ;

        Event::assertDispatched(fn (UpdateInventoryEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            UpdateInventoryEvent::class,
            SyncEComUpdateInventoryListener::class
        );
    }

    /** @test */
    public function success_update_check_event_not_send_to_ecomm()
    {
        Event::fake([UpdateInventoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->for_shop(false)->create();

        $data = $this->data;
        $data['for_shop'] = false;

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                ],
            ])
        ;

        Event::assertNotDispatched(UpdateInventoryEvent::class);
    }

    /** @test */
    public function success_update_feature()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $feature_1 = $this->featureBuilder->position(1)->create();
        $value_1_1 = $this->featureValueBuilder->feature($feature_1)->create();

        $feature_2 = $this->featureBuilder->position(1)->create();
        $value_2_1 = $this->featureValueBuilder->feature($feature_2)->create();
        $value_2_2 = $this->featureValueBuilder->feature($feature_2)->create();

        $feature_3 = $this->featureBuilder->position(1)->create();
        $value_3_1 = $this->featureValueBuilder->feature($feature_3)->create();

        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_1)->value($value_1_1)->create();
        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_2)->value($value_2_1)->create();
        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_2)->value($value_2_2)->create();

        $data = $this->data;
        $data['features'] = [
            [
                'feature_id' => $feature_3->id,
                'value_ids' => [
                    $value_3_1->id
                ]
            ]
        ];

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'features' => [
                        [
                            'id' => $feature_3->id,
                            'values' => [
                                ['id' => $value_3_1->id]
                            ]
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.features')
            ->assertJsonCount(1, 'data.features.0.values')
        ;
    }

    /** @test */
    public function success_update_feature_as_one()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $feature_1 = $this->featureBuilder->position(1)->create();
        $value_1_1 = $this->featureValueBuilder->feature($feature_1)->create();

        $feature_2 = $this->featureBuilder->position(1)->create();
        $value_2_1 = $this->featureValueBuilder->feature($feature_2)->create();
        $value_2_2 = $this->featureValueBuilder->feature($feature_2)->create();

        $feature_3 = $this->featureBuilder->position(1)->create();
        $value_3_1 = $this->featureValueBuilder->feature($feature_3)->create();

        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_1)->value($value_1_1)->create();
        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_2)->value($value_2_1)->create();
        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_2)->value($value_2_2)->create();

        $data = $this->data;
        $data['features'] = [
            [
                'feature_id' => $feature_3->id,
                'value_id' => $value_3_1->id
            ]
        ];

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'features' => [
                        [
                            'id' => $feature_3->id,
                            'values' => [
                                ['id' => $value_3_1->id]
                            ]
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.features')
            ->assertJsonCount(1, 'data.features.0.values')
        ;
    }

    /** @test */
    public function success_update_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $brand Brand */
        $brand = $this->brandBuilder->create();
        /** @var $supplier Supplier */
        $supplier = $this->supplierBuilder->create();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->price_retail(100)
            ->min_limit_price(10)
            ->brand(null)
            ->create();
        $old = clone $model;

        $data = $this->data;
        $data['category_id'] = null;
        $data['brand_id'] = $brand->id;
        $data['supplier_id'] = $supplier->id;

        $data['name'] = $model->name;
        $data['slug'] = $model->slug;
        $data['stock_number'] = $model->stock_number;
        $data['article_number'] = $model->article_number;
        $data['price_retail'] = $model->price_retail;
        $data['length'] = $model->length;
        $data['width'] = $model->width;
        $data['height'] = $model->height;
        $data['package_type'] = $model->package_type->value;
        $data['notes'] = $model->notes;
        $data['for_shop'] = $model->for_shop;
        $data['min_limit_price'] = $model->min_limit_price;
        $data['is_popular'] = $model->is_popular;
        $data['is_new'] = $model->is_new;
        $data['delivery_cost'] = $model->delivery_cost;

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->json('data.id')
        ;

        $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.updated');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $model->stock_number,
            'inventory_name' => $model->name,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            'category_id' => [
                'old' => $old->category->name,
                'new' => null,
                'type' => 'updated',
            ],
            'unit_id' => [
                'old' => $old->unit->name,
                'new' => Unit::find(data_get($data, 'unit_id'))->name,
                'type' => 'updated',
            ],
            'supplier_id' => [
                'old' => $old->supplier->name,
                'new' => $supplier->name,
                'type' => 'updated',
            ],
            'min_limit' => [
                'old' => $old->min_limit,
                'new' => data_get($data, 'min_limit'),
                'type' => 'updated',
            ],
            'weight' => [
                'old' => $old->weight,
                'new' => data_get($data, 'weight'),
                'type' => 'updated',
            ],
            'brand_id' => [
                'old' => null,
                'new' => $brand->name,
                'type' => 'updated',
            ],
            'is_sale' => [
                'old' => $old->is_stock,
                'new' => data_get($data, 'is_sale'),
                'type' => 'updated',
            ],
            'old_price' => [
                'old' => $old->old_price,
                'new' => data_get($data, 'old_price'),
                'type' => 'updated',
            ],
            'discount' => [
                'old' => $old->discount,
                'new' => data_get($data, 'discount'),
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function success_update_add_main_image()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $img_1 = UploadedFile::fake()->image('img_1.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data = $this->data;
        $data[Inventory::MAIN_IMAGE_FIELD_NAME] = $img_1;

        $this->assertNull($model->getMainImg());

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(0, 'data.gallery')
        ;

        $model->refresh();
        $this->assertNotNull($model->getMainImg());

        $history = $model->histories[0];

        $this->assertEquals(
            $history->details[Inventory::MAIN_IMAGE_FIELD_NAME.'.'.$model->getMainImg()->id.'.name']['new'],
            $model->getMainImg()->name
        );
        $this->assertEquals(
            $history->details[Inventory::MAIN_IMAGE_FIELD_NAME.'.'.$model->getMainImg()->id.'.name']['type'],
            'added'
        );
        $this->assertNull(
            $history->details[Inventory::MAIN_IMAGE_FIELD_NAME.'.'.$model->getMainImg()->id.'.name']['old']
        );
    }

    /** @test */
    public function success_update_change_main_image()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $img_1 = UploadedFile::fake()->image('img_1.png');
        $img_2 = UploadedFile::fake()->image('img_2.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->mainImg($img_1)->create();

        $data = $this->data;
        $data[Inventory::MAIN_IMAGE_FIELD_NAME] = $img_2;

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(0, 'data.gallery')
        ;

        $model->refresh();
        $this->assertEquals($model->getMainImg()->name, 'img_2');

        $history = $model->histories[0];

        $this->assertEquals(
            $history->details[Inventory::MAIN_IMAGE_FIELD_NAME.'.'.$model->getMainImg()->id.'.name']['new'],
            $model->getMainImg()->name
        );
        $this->assertEquals(
            $history->details[Inventory::MAIN_IMAGE_FIELD_NAME.'.'.$model->getMainImg()->id.'.name']['type'],
            'added'
        );
        $this->assertNull(
            $history->details[Inventory::MAIN_IMAGE_FIELD_NAME.'.'.$model->getMainImg()->id.'.name']['old']
        );
    }

    /** @test */
    public function success_update_add_gallery()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $img_1 = UploadedFile::fake()->image('img_1.png');
        $img_2 = UploadedFile::fake()->image('img_2.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data = $this->data;
        $data[Inventory::GALLERY_FIELD_NAME] = [
            $img_1,
            $img_2,
        ];

        $this->assertEmpty($model->getGallery());

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(2, 'data.gallery')
        ;

        $model->refresh();
        $this->assertNotEmpty($model->getGallery());

        $history = $model->histories[0];

        foreach ($model->getGallery() as $media){
            $this->assertEquals(
                $history->details[Inventory::GALLERY_FIELD_NAME.'.'.$media->id.'.name']['new'],
                $media->name
            );
            $this->assertEquals(
                $history->details[Inventory::GALLERY_FIELD_NAME.'.'.$media->id.'.name']['type'],
                'added'
            );
            $this->assertNull(
                $history->details[Inventory::GALLERY_FIELD_NAME.'.'.$media->id.'.name']['old']
            );
        }
    }

    /** @test */
    public function success_update_change_gallery()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $img_1 = UploadedFile::fake()->image('img_1.png');
        $img_2 = UploadedFile::fake()->image('img_2.png');
        $img_3 = UploadedFile::fake()->image('img_3.png');

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->gallery($img_1, $img_2)->create();

        $data = $this->data;
        $data[Inventory::GALLERY_FIELD_NAME] = [
            $img_3,
        ];

        $this->assertCount(2, $model->getGallery());

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(3, 'data.gallery')
        ;

        $model->refresh();
        $this->assertCount(3, $model->getGallery());
        $this->assertEquals($model->getGallery()[2]->name, 'img_3');

        $history = $model->histories[0];

        foreach ($model->getGallery() as $media){
            $this->assertEquals(
                $history->details[Inventory::GALLERY_FIELD_NAME.'.'.$media->id.'.name']['new'],
                $media->name
            );
            $this->assertEquals(
                $history->details[Inventory::GALLERY_FIELD_NAME.'.'.$media->id.'.name']['type'],
                'added'
            );
            $this->assertNull(
                $history->details[Inventory::GALLERY_FIELD_NAME.'.'.$media->id.'.name']['old']
            );
        }
    }

    /** @test */
    public function success_update_nothing_change()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->discount(1)->old_price(1)->create();

        $data = $this->data;
        $data['name'] = $model->name;
        $data['slug'] = $model->slug;
        $data['stock_number'] = $model->stock_number;
        $data['article_number'] = $model->article_number;
        $data['unit_id'] = $model->unit_id;
        $data['category_id'] = $model->category_id;
        $data['supplier_id'] = $model->supplier_id;
        $data['brand_id'] = $model->brand_id;
        $data['price_retail'] = $model->price_retail;
        $data['min_limit'] = $model->min_limit;
        $data['notes'] = $model->notes;
        $data['for_shop'] = $model->for_shop;
        $data['length'] = $model->length;
        $data['width'] = $model->width;
        $data['height'] = $model->height;
        $data['weight'] = $model->weight;
        $data['package_type'] = $model->package_type->value;
        $data['min_limit_price'] = $model->min_limit_price;
        $data['is_new'] = $model->is_new;
        $data['is_popular'] = $model->is_popular;
        $data['is_sale'] = $model->is_sale;
        $data['discount'] = $model->discount;
        $data['old_price'] = $model->old_price;
        $data['delivery_cost'] = $model->delivery_cost;

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model->refresh();
        $this->assertEmpty($model->histories);
    }

    /** @test */
    public function success_update_seo()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $img = UploadedFile::fake()->image('img.png');

        $seo = $this->seoBuilder->model($model)->image($img)->create();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'seo h1',
            'title' => 'seo title',
            'keywords' => 'seo keywords',
            'desc' => 'seo desc',
            'text' => 'seo text',
        ];

        $this->assertNotEquals($model->seo->h1, data_get($data, 'seo.h1'));
        $this->assertNotEquals($model->seo->title, data_get($data, 'seo.title'));
        $this->assertNotEquals($model->seo->desc, data_get($data, 'seo.desc'));
        $this->assertNotEquals($model->seo->keywords, data_get($data, 'seo.keywords'));
        $this->assertNotEquals($model->seo->text, data_get($data, 'seo.text'));

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'seo' => [
                        'h1' => data_get($data, 'seo.h1'),
                        'title' => data_get($data, 'seo.title'),
                        'keywords' => data_get($data, 'seo.keywords'),
                        'desc' => data_get($data, 'seo.desc'),
                        'text' => data_get($data, 'seo.text'),
                    ]
                ],
            ])
        ;

        $seo->refresh();

        $this->assertEquals($seo->media[0]->name, 'img');
    }

    /** @test */
    public function success_update_seo_and_update_seo_image()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $img = UploadedFile::fake()->image('img.png');
        $img_1 = UploadedFile::fake()->image('img_1.png');

        $seo = $this->seoBuilder->model($model)->image($img)->create();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'category h1',
            'image' => $img_1,
        ];

        $this->assertNotEquals($seo->media[0]->name, 'img_1');

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'seo' => [
                        'image' => [
                            'id',
                            'original',
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'seo' => [
                        'h1' => data_get($data, 'seo.h1'),
                        'title' => null,
                        'keywords' => null,
                        'desc' => null,
                        'text' => null,
                    ]
                ],
            ])
        ;

        $seo->refresh();

        $this->assertEquals($seo->media[0]->name, 'img_1');
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertNotEquals($model->name, data_get($data, 'name'));
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $this->inventoryBuilder->slug('slug')
            ->stock_number('11222111')
            ->article_number('21222111')
            ->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['name', null, 'validation.required', ['attribute' => 'validation.attributes.name']],
            ['slug', null, 'validation.required', ['attribute' => 'validation.attributes.slug']],
            ['slug', 1111, 'validation.string', ['attribute' => 'validation.attributes.slug']],
            ['slug', 'slug', 'validation.unique', ['attribute' => 'validation.attributes.slug']],
            ['stock_number', '11222111', 'validation.unique', ['attribute' => 'validation.attributes.stock_number']],
            ['article_number', null, 'validation.required', ['attribute' => 'validation.attributes.article_number']],
            ['article_number', '21222111', 'validation.unique', ['attribute' => 'validation.attributes.article_number']],
            ['unit_id', null, 'validation.required', ['attribute' => 'validation.attributes.unit_id']],
            ['unit_id', 0, 'validation.exists', ['attribute' => 'validation.attributes.unit_id']],
            ['category_id', 0, 'validation.exists', ['attribute' => 'validation.attributes.category_id']],
            ['brand_id', 0, 'validation.exists', ['attribute' => 'validation.attributes.brand_id']],
            ['supplier_id', 0, 'validation.exists', ['attribute' => 'validation.attributes.supplier_id']],
            ['for_shop', 'true', 'validation.boolean', ['attribute' => 'validation.attributes.for_shop']],
            ['length', 'ss', 'validation.numeric', ['attribute' => 'validation.attributes.length']],
            ['width', 'ss', 'validation.numeric', ['attribute' => 'validation.attributes.width']],
            ['height', 'ss', 'validation.numeric', ['attribute' => 'validation.attributes.height']],
            ['weight', 'ss', 'validation.numeric', ['attribute' => 'validation.attributes.weight']],
            ['package_type', 'ss', 'validation.in', ['attribute' => 'validation.attributes.package_type']],
            ['min_limit_price', 'ss', 'validation.numeric', ['attribute' => 'validation.attributes.min_limit_price']],
//            ['min_limit', 1.9, 'validation.integer', ['attribute' => 'min_limit']],
            ['price_retail', 'ss', 'validation.numeric', ['attribute' => 'validation.attributes.price_retail']],
        ];
    }

    /**
     * @dataProvider dimension_data
     * @test
     */
    public function fail_required_dimension_if_for_shop($field, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->width(null)
            ->length(null)
            ->height(null)
            ->weight(null)
            ->package_type(null)
            ->create();

        $data = $this->data;
        $data['for_shop'] = true;
        $data[$field] = null;

        $res = $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function dimension_data(): array
    {
        return [
            ['length', 'validation.required_if', ['attribute' => 'validation.attributes.length', 'other' => 'validation.attributes.for_shop', 'value' => 'true']],
            ['width', 'validation.required_if', ['attribute' => 'validation.attributes.width', 'other' => 'validation.attributes.for_shop', 'value' => 'true']],
            ['height', 'validation.required_if', ['attribute' => 'validation.attributes.height', 'other' => 'validation.attributes.for_shop', 'value' => 'true']],
            ['weight', 'validation.required_if', ['attribute' => 'validation.attributes.weight', 'other' => 'validation.attributes.for_shop', 'value' => 'true']],
            ['package_type', 'validation.required_if', ['attribute' => 'validation.attributes.package_type', 'other' => 'validation.attributes.for_shop', 'value' => 'true']],
        ];
    }

    /** @test */
    public function not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.update', ['id' => 999999]), $data);

        self::assertErrorMsg($res, __("exceptions.inventories.inventory.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->postJson(route('api.v1.inventories.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
