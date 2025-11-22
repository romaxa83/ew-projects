<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Crud;

use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Inventory;
use App\Models\Suppliers\SupplierContact;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\InventoryFeatureValueBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkInventoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\Builders\Suppliers\SupplierContactBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkInventoryBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected SupplierBuilder $supplierBuilder;
    protected SupplierContactBuilder $supplierContactBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected SeoBuilder $seoBuilder;
    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;
    protected InventoryFeatureValueBuilder $inventoryFeatureValueBuilder;
    protected OrderBuilder $orderBuilder;
    protected OrderTypeOfWorkBuilder $orderTypeOfWorkBuilder;
    protected OrderTypeOfWorkInventoryBuilder $orderTypeOfWorkInventoryBuilder;

    protected TypeOfWorkBuilder $typeOfWorkBuilder;
    protected TypeOfWorkInventoryBuilder $typeOfWorkInventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
        $this->supplierBuilder = resolve(SupplierBuilder::class);
        $this->supplierContactBuilder = resolve(SupplierContactBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
        $this->inventoryFeatureValueBuilder = resolve(InventoryFeatureValueBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->orderTypeOfWorkBuilder = resolve(OrderTypeOfWorkBuilder::class);
        $this->orderTypeOfWorkInventoryBuilder = resolve(OrderTypeOfWorkInventoryBuilder::class);
        $this->typeOfWorkBuilder = resolve(TypeOfWorkBuilder::class);
        $this->typeOfWorkInventoryBuilder = resolve(TypeOfWorkInventoryBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $supplier = $this->supplierBuilder->create();
        /** @var $supplierContact SupplierContact */
        $supplierContact = $this->supplierContactBuilder->supplier($supplier)->create();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->supplier($supplier)
            ->mainImg(UploadedFile::fake()->image('img.png'))
            ->create();

        /** @var $seo Seo */
        $seo = $this->seoBuilder->model($model)->create();

        $feature_1 = $this->featureBuilder->position(2)->multiple(false)->create();
        $value_1_1 = $this->featureValueBuilder->feature($feature_1)->position(1)->create();
        $value_1_2 = $this->featureValueBuilder->feature($feature_1)->position(3)->create();

        $feature_2 = $this->featureBuilder->position(1)->multiple(true)->create();
        $value_2_1 = $this->featureValueBuilder->feature($feature_2)->position(1)->create();
        $value_2_2 = $this->featureValueBuilder->feature($feature_2)->position(3)->create();

        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_1)->value($value_1_2)->create();
        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_2)->value($value_2_1)->create();
        $this->inventoryFeatureValueBuilder->inventory($model)->feature($feature_2)->value($value_2_2)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'stock_number' => $model->stock_number,
                    'article_number' => $model->article_number,
                    'price_retail' => $model->price_retail,
                    'quantity' => $model->quantity,
                    'min_limit' => $model->min_limit,
                    'for_shop' => $model->for_shop,
                    'status' => $model->getStatus(),
                    'brand' => [
                        'id' => $model->brand->id,
                        'name' => $model->brand->name,
                    ],
                    'category_id' => $model->category_id,
                    'category' => [
                        'id' => $model->category->id,
                        'name' => $model->category->name,
                    ],
                    'supplier_id' => $model->supplier_id,
                    'supplier' => [
                        'id' => $model->supplier->id,
                        'name' => $model->supplier->name,
                        'url' => $model->supplier->url,
                        'contact' => [
                            'name' => $supplierContact->name,
                            'email' => $supplierContact->email->getValue(),
                            'phone' => $supplierContact->phone->getValue(),
                            'phone_extension' => $supplierContact->phone_extension,
                            'position' => $supplierContact->position,
                        ]
                    ],
                    'notes' => $model->notes,
                    'unit_id' => $model->unit_id,
                    'unit' => [
                        'id' => $model->unit->id,
                        'name' => $model->unit->name,
                        'accept_decimals' => $model->unit->accept_decimals,
                    ],
                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => false,
                    'hasRelatedTypesOfWork' => false,
                    'length' => $model->length,
                    'width' => $model->width,
                    'height' => $model->height,
                    'weight' => $model->weight,
                    'package_type' => $model->package_type->value,
                    'min_limit_price' => $model->min_limit_price,
                    'is_new' => $model->is_new,
                    'is_popular' => $model->is_popular,
                    'is_sale' => $model->is_sale,
                    'old_price' => $model->old_price,
                    'discount' => $model->discount,
                    'delivery_cost' => $model->delivery_cost,
                    'main_image' => [
                        'id' => $model->getMainImg()->id
                    ],
                    'gallery' => [],
                    'seo' => [
                        'h1' => $seo->h1,
                        'title' => $seo->title,
                        'keywords' => $seo->keywords,
                        'desc' => $seo->desc,
                        'text' => $seo->text,
                        'image' =>  null,
                    ],
                    'features' => [
                        [
                            'id' => $feature_2->id,
                            'values' => [
                                ['id' => $value_2_1->id],
                                ['id' => $value_2_2->id],
                            ]
                        ],
                        [
                            'id' => $feature_1->id,
                            'values' => ['id' => $value_1_2->id]
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(0, 'data.gallery')
        ;
    }

    /** @test */
    public function success_show_not_feature_value()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create();

        $feature_1 = $this->featureBuilder->position(2)->multiple(false)->create();
        $value_1_1 = $this->featureValueBuilder->feature($feature_1)->position(1)->create();
        $value_1_2 = $this->featureValueBuilder->feature($feature_1)->position(3)->create();

        $feature_2 = $this->featureBuilder->position(1)->multiple(true)->create();
        $value_2_1 = $this->featureValueBuilder->feature($feature_2)->position(1)->create();
        $value_2_2 = $this->featureValueBuilder->feature($feature_2)->position(3)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'notes' => $model->notes,
                    'features' => []
                ],
            ])
            ->assertJsonCount(0, 'data.features')
        ;
    }

    /** @test */
    public function success_show_has_open_order_as_new()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create();

        $order = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                    'hasRelatedOpenOrders' => true,
                    'hasRelatedDeletedOrders' => false,
                    'hasRelatedTypesOfWork' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_open_order_as_in_process()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create();

        $order = $this->orderBuilder->status(OrderStatus::In_process->value)->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                    'hasRelatedOpenOrders' => true,
                    'hasRelatedDeletedOrders' => false,
                    'hasRelatedTypesOfWork' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_open_order_as_finish_now()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create();

        $order = $this->orderBuilder->status(OrderStatus::Finished->value, CarbonImmutable::now())->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'hasRelatedOpenOrders' => true,
                    'hasRelatedDeletedOrders' => false,
                    'hasRelatedTypesOfWork' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_open_order_as_finish_no_now()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create();

        $order = $this->orderBuilder->status(OrderStatus::Finished->value, CarbonImmutable::now()->subDays(3))->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => false,
                    'hasRelatedTypesOfWork' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_deleted_order()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create();

        $order = $this->orderBuilder->status(OrderStatus::Deleted->value)->deleted()->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => true,
                    'hasRelatedTypesOfWork' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_type_of_work()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder
            ->create();

        $work = $this->typeOfWorkBuilder->create();
        $this->typeOfWorkInventoryBuilder->inventory($model)->work($work)->create();

        $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,

                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => false,
                    'hasRelatedTypesOfWork' => true,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.inventory.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
