<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Crud;

use App\Enums\Inventories\InventoryPackageType;
use App\Enums\Inventories\InventoryStockStatus;
use App\Enums\Inventories\Transaction\OperationType;
use App\Events\Events\Inventories\Inventories\CreateInventoryEvent;
use App\Events\Listeners\Inventories\Inventories\SyncEComCreateInventoryListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\UnitBuilder;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected CategoryBuilder $categoryBuilder;
    protected UnitBuilder $unitBuilder;
    protected SupplierBuilder $supplierBuilder;
    protected BrandBuilder $brandBuilder;
    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        $this->brandBuilder = resolve(BrandBuilder::class);
        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->unitBuilder = resolve(UnitBuilder::class);
        $this->supplierBuilder = resolve(SupplierBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);

        parent::setUp();

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
            'for_shop' => true,
            'length' => 10,
            'width' => 2.9,
            'height' => 4.0,
            'weight' => 5,
            'package_type' => InventoryPackageType::Carrier->value,
            'min_limit_price' => 9,
            'is_new' => true,
            'is_popular' => true,
            'is_sale' => true,
            'discount' => 9,
            'delivery_cost' => 1.9,
            'old_price' => 9.10,
            'purchase' => [
                'quantity' => 3,
                'date' => "02/10/2004",
                'cost' => 3.9,
                'invoice_number' => '3TYYYY4',
            ]
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([CreateInventoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $category Category */
        $category = $this->categoryBuilder->create();
        /** @var $brand Brand */
        $brand = $this->brandBuilder->create();
        /** @var $supplier Supplier */
        $supplier = $this->supplierBuilder->create();

        $data = $this->data;
        $data['category_id'] = $category->id;
        $data['brand_id'] = $brand->id;
        $data['supplier_id'] = $supplier->id;

        $id = $this->postJson(route('api.v1.inventories.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'slug' => data_get($data, 'slug'),
                    'stock_number' => data_get($data, 'stock_number'),
                    'article_number' => data_get($data, 'article_number'),
                    'price_retail' => data_get($data, 'price_retail'),
                    'quantity' => data_get($data, 'purchase.quantity'),
                    'min_limit' => data_get($data, 'min_limit'),
                    'for_shop' => data_get($data, 'for_shop'),
                    'status' => InventoryStockStatus::IN->value,
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                    'unit_id' => data_get($data, 'unit_id'),
                    'brand' => [
                        'id' => $brand->id
                    ],
                    'notes' => data_get($data, 'notes'),
                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => false,
                    'hasRelatedTypesOfWork' => false,
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
                    'delivery_cost' => data_get($data, 'delivery_cost'),
                    'old_price' => data_get($data, 'old_price'),
                    'main_image' => null,
                    'gallery' => [],
                    'seo' => [
                        'h1' => null,
                        'title' => null,
                        'keywords' => null,
                        'desc' => null,
                        'text' => null,
                        'image' => null,
                    ],
                    'features' => []
                ],
            ])
            ->json('data.id')
        ;

        $model = Inventory::find($id);

        $this->assertTrue($model->transactions[0]->operation_type->isPurchase(), OperationType::PURCHASE->value);
        $this->assertEquals($model->transactions[0]->invoice_number, data_get($data, 'purchase.invoice_number'));
        $this->assertEquals($model->transactions[0]->quantity, data_get($data, 'purchase.quantity'));
        $this->assertEquals($model->transactions[0]->price, data_get($data, 'purchase.cost'));

        Event::assertDispatched(fn (CreateInventoryEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(
            CreateInventoryEvent::class,
            SyncEComCreateInventoryListener::class
        );
    }

    /** @test */
    public function success_create_with_feature()
    {
        $this->loginUserAsSuperAdmin();

        $feature_1 = $this->featureBuilder->name('feature_1')->position(1)->create();
        $value_1_1 = $this->featureValueBuilder->name('value_1_1')
            ->position(2)
            ->feature($feature_1)->create();
        $value_1_2 = $this->featureValueBuilder->name('value_1_2')
            ->position(1)
            ->feature($feature_1)->create();
        $value_1_3 = $this->featureValueBuilder->name('value_1_3')->feature($feature_1)->create();

        $feature_2 = $this->featureBuilder->name('feature_2')->position(2)->create();
        $value_2_1 = $this->featureValueBuilder
            ->name('value_2_1')
            ->feature($feature_2)
            ->create();

        $data = $this->data;
        $data['features'] = [
            [
                'feature_id' => $feature_1->id,
                'value_ids' => [
                    $value_1_1->id,
                    $value_1_2->id,
                ]
            ],
            [
                'feature_id' => $feature_2->id,
                'value_id' => $value_2_1->id,
            ]
        ];

        $this->postJson(route('api.v1.inventories.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'features' => [
                        [
                            'id' => $feature_1->id,
                            'values' => [
                                ['id' => $value_1_2->id],
                                ['id' => $value_1_1->id],
                            ]
                        ],
                        [
                            'id' => $feature_2->id,
                            'values' => [
                                ['id' => $value_2_1->id],
                            ]
                        ]
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function fail_create_required_feature_id()
    {
        $this->loginUserAsSuperAdmin();

        $feature_1 = $this->featureBuilder->name('feature_1')->position(1)->create();
        $value_1_1 = $this->featureValueBuilder->name('value_1_1')
            ->position(2)
            ->feature($feature_1)->create();
        $value_1_2 = $this->featureValueBuilder->name('value_1_2')
            ->position(1)
            ->feature($feature_1)->create();

        $data = $this->data;
        $data['features'] = [
            [
                'feature_id' => null,
                'value_ids' => [
                    $value_1_1->id,
                    $value_1_2->id,
                ]
            ],
        ];

        $res = $this->postJson(route('api.v1.inventories.store'), $data)
        ;

        self::assertValidationMsg($res,
            __('validation.required', ['attribute' => __('validation.attributes.feature_id')]),
            'features.0.feature_id'
        );
    }

    /** @test */
    public function fail_create_required_value_ids()
    {
        $this->loginUserAsSuperAdmin();

        $feature_1 = $this->featureBuilder->name('feature_1')->position(1)->create();

        $data = $this->data;
        $data['features'] = [
            [
                'feature_id' => $feature_1->id,
            ],
        ];

        $res = $this->postJson(route('api.v1.inventories.store'), $data)
        ;

        self::assertValidationMsg($res,
            __('validation.required_without', [
                'attribute' => __('validation.attributes.value_id'),
                'values' => __('validation.attributes.value_id'),
            ]),
            'features.0.value_id'
        );
    }

    /** @test */
    public function success_create_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $category Category */
        $category = $this->categoryBuilder->create();
        /** @var $brand Brand */
        $brand = $this->brandBuilder->create();
        /** @var $supplier Supplier */
        $supplier = $this->supplierBuilder->create();

        $data = $this->data;
        $data['category_id'] = $category->id;
        $data['brand_id'] = $brand->id;
        $data['supplier_id'] = $supplier->id;

        $id = $this->postJson(route('api.v1.inventories.store'), $data)
            ->json('data.id')
        ;

        /** @var $model Inventory */
        $model = Inventory::find($id);
        $history = $model->histories->where('msg', 'history.inventory.created')->first();

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.created');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $model->stock_number,
            'inventory_name' => $model->name,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            'name' => [
                'old' => null,
                'new' => data_get($data, 'name'),
                'type' => 'added',
            ],
            'slug' => [
                'old' => null,
                'new' => data_get($data, 'slug'),
                'type' => 'added',
            ],
            'stock_number' => [
                'old' => null,
                'new' => data_get($data, 'stock_number'),
                'type' => 'added',
            ],
            'article_number' => [
                'old' => null,
                'new' => data_get($data, 'article_number'),
                'type' => 'added',
            ],
            'category_id' => [
                'old' => null,
                'new' => $category->name,
                'type' => 'added',
            ],
            'brand_id' => [
                'old' => null,
                'new' => $brand->name,
                'type' => 'added',
            ],
            'supplier_id' => [
                'old' => null,
                'new' => $supplier->name,
                'type' => 'added',
            ],
            'unit_id' => [
                'old' => null,
                'new' => Unit::find(data_get($data, 'unit_id'))->name,
                'type' => 'added',
            ],
            'price_retail' => [
                'old' => null,
                'new' => data_get($data, 'price_retail'),
                'type' => 'added',
            ],
            'min_limit' => [
                'old' => null,
                'new' => data_get($data, 'min_limit'),
                'type' => 'added',
            ],
            'min_limit_price' => [
                'old' => null,
                'new' => data_get($data, 'min_limit_price'),
                'type' => 'added',
            ],
            'notes' => [
                'old' => null,
                'new' => data_get($data, 'notes'),
                'type' => 'added',
            ],
            'for_shop' => [
                'old' => null,
                'new' => data_get($data, 'for_shop'),
                'type' => 'added',
            ],
            'length' => [
                'old' => null,
                'new' => data_get($data, 'length'),
                'type' => 'added',
            ],
            'width' => [
                'old' => null,
                'new' => data_get($data, 'width'),
                'type' => 'added',
            ],
            'height' => [
                'old' => null,
                'new' => data_get($data, 'height'),
                'type' => 'added',
            ],
            'weight' => [
                'old' => null,
                'new' => data_get($data, 'weight'),
                'type' => 'added',
            ],
            'package_type' => [
                'old' => null,
                'new' => data_get($data, 'package_type'),
                'type' => 'added',
            ],
            'is_new' => [
                'old' => null,
                'new' => data_get($data, 'is_new'),
                'type' => 'added',
            ],
            'is_popular' => [
                'old' => null,
                'new' => data_get($data, 'is_popular'),
                'type' => 'added',
            ],
            'is_sale' => [
                'old' => null,
                'new' => data_get($data, 'is_sale'),
                'type' => 'added',
            ],
            'old_price' => [
                'old' => null,
                'new' => data_get($data, 'old_price'),
                'type' => 'added',
            ],
            'discount' => [
                'old' => null,
                'new' => data_get($data, 'discount'),
                'type' => 'added',
            ],
            'delivery_cost' => [
                'old' => null,
                'new' => data_get($data, 'delivery_cost'),
                'type' => 'added',
            ],
            'quantity' => [
                'old' => null,
                'new' => 0,
                'type' => 'added',
            ],
        ]);
    }

    /** @test */
    public function success_create_check_history_purchase()
    {
        $user = $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $id = $this->postJson(route('api.v1.inventories.store'), $data)
            ->json('data.id')
        ;

        /** @var $model Inventory */
        $model = Inventory::find($id);
        $history = $model->histories[1];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_increased');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $model->stock_number,
            'inventory_name' => $model->name,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            'quantity' => [
                'old' => 0,
                'new' => data_get($data, 'purchase.quantity'),
                'type' => 'updated',
            ],
        ]);
    }

    /** @test */
    public function success_create_with_main_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Inventory::MAIN_IMAGE_FIELD_NAME] = UploadedFile::fake()->image('img.png');

        $id = $this->postJson(route('api.v1.inventories.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    Inventory::MAIN_IMAGE_FIELD_NAME => [
                        'id',
                        'original',
                        'sm',
                    ]
                ],
            ])
            ->assertJsonCount(0, 'data.gallery')
            ->json('data.id')
        ;

        /** @var $model Inventory */
        $model = Inventory::find($id);
        $history = $model->histories[0];

        $this->assertEquals(
            $history->details[Inventory::MAIN_IMAGE_FIELD_NAME.'.'.$model->getMainImg()->id.'.name']['new'],
            'img'
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
    public function success_create_with_main_img_and_gallery()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data[Inventory::MAIN_IMAGE_FIELD_NAME] = UploadedFile::fake()->image('img.png');
        $data[Inventory::GALLERY_FIELD_NAME] = [
            UploadedFile::fake()->image('img_1.png'),
            UploadedFile::fake()->image('img_2.png'),
        ];

        $id = $this->postJson(route('api.v1.inventories.store'), $data)
            ->assertJsonStructure([
                'data' => [
                    Inventory::MAIN_IMAGE_FIELD_NAME => [
                        'id',
                        'original',
                        'sm',
                    ],
                    Inventory::GALLERY_FIELD_NAME => [
                        [
                            'id',
                            'original',
                            'sm',
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(2, 'data.gallery')
            ->json('data.id')
        ;

        /** @var $model Inventory */
        $model = Inventory::find($id);
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
    public function success_create_only_required_fields()
    {
        Event::fake([CreateInventoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data['purchase']['quantity'] = 0;
        unset(
            $data['price_retail'],
            $data['min_limit'],
            $data['for_shop'],
            $data['length'],
            $data['width'],
            $data['height'],
            $data['weight'],
            $data['min_limit_price'],
            $data['notes'],
            $data['is_new'],
            $data['is_popular'],
            $data['is_sale'],
            $data['old_price'],
            $data['discount'],
            $data['package_type'],
            $data['delivery_cost'],
        );

        $this->postJson(route('api.v1.inventories.store'), $data)
            ->assertJson([
                'data' => [
                    'price_retail' => null,
                    'quantity' => 0,
                    'min_limit' => null,
                    'for_shop' => false,
                    'status' => InventoryStockStatus::OUT->value,
                    'category_id' => null,
                    'supplier_id' =>null,
                    'brand' => null,
                    'notes' => null,
                    'length' => null,
                    'width' => null,
                    'height' => null,
                    'weight' => null,
                    'package_type' => null,
                    'min_limit_price' => null,
                    'is_new' => false,
                    'is_popular' => false,
                    'is_sale' => false,
                    'old_price' => null,
                    'discount' => null,
                    'delivery_cost' => null,
                ],
            ])
        ;

        Event::assertNotDispatched(CreateInventoryEvent::class);
    }

    /** @test */
    public function success_create_with_seo()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'seo h1',
            'title' => 'seo title',
            'keywords' => 'seo keywords',
            'desc' => 'seo desc',
            'text' => 'seo text',
        ];

        $this->postJson(route('api.v1.inventories.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
                    'seo' => [
                        'h1' => data_get($data, 'seo.h1'),
                        'title' => data_get($data, 'seo.title'),
                        'keywords' => data_get($data, 'seo.keywords'),
                        'desc' => data_get($data, 'seo.desc'),
                        'text' => data_get($data, 'seo.text'),
                        'image' => null,
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_create_with_seo_and_seo_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $data['seo'] = [
            'h1' => 'seo h1',
            'image' => UploadedFile::fake()->image('img.png'),
        ];

        $this->postJson(route('api.v1.inventories.store'), $data)
            ->assertJson([
                'data' => [
                    'name' => data_get($data, 'name'),
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
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data['name'] = null;

        $res = $this->postJson(route('api.v1.inventories.store'), $data, [
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

        $data = $this->data;

        $this->postJson(route('api.v1.inventories.store'), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertFalse(Inventory::query()->where('slug', $data['slug'])->exists());
    }

    /** @test */
    public function fail_if_check_is_stock_but_not_old_price()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        unset(
            $data['old_price']
        );

        $res = $this->postJson(route('api.v1.inventories.store'), $data);

        $this->assertValidationMsg($res,
            __("validation.required", ["attribute" => __("validation.attributes.old_price")]),
            'old_price');
    }

    /** @test */
    public function fail_if_check_is_stock_but_not_discount()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        unset(
            $data['discount']
        );

        $res = $this->postJson(route('api.v1.inventories.store'), $data);

        $this->assertValidationMsg($res,
            __("validation.required", ["attribute" => __("validation.attributes.discount")]),
            'discount');
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->inventoryBuilder->slug('slug')
            ->stock_number('11222111')
            ->article_number('21222111')
            ->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.store'), $data)
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
//            ['min_limit', 1.9, 'validation.integer', ['attribute' => 'validation.attributes.min_limit']],
            ['price_retail', 'ss', 'validation.numeric', ['attribute' => 'validation.attributes.price_retail']],
        ];
    }

    /**
     * @dataProvider validate_purchase
     * @test
     */
    public function validate_data_purchase($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->inventoryBuilder->slug('slug')
            ->stock_number('11222111')->create();

        $data = $this->data;
        $data['purchase'][$field] = $value;

        $res = $this->postJson(route('api.v1.inventories.store'), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, 'purchase.'.$field, $attributes);
    }

    public static function validate_purchase(): array
    {
        return [
            ['quantity', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.quantity']],
            ['cost', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.cost']],
            ['date', null, 'validation.required', ['attribute' => 'validation.attributes.purchase.date']],
        ];
    }

    /**
     * @dataProvider dimension_data
     * @test
     */
    public function fail_required_dimension_if_for_shop($field, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        unset(
            $data[$field],
        );

        $res = $this->postJson(route('api.v1.inventories.store'), $data)
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
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.store'), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        $res = $this->postJson(route('api.v1.inventories.store'), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
