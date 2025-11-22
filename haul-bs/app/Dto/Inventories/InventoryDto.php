<?php

namespace App\Dto\Inventories;

use App\Foundations\Modules\Seo\Dto\SeoDto;
use App\Models\Inventories\Inventory;
use Illuminate\Http\UploadedFile;

class InventoryDto
{
    public string $name;
    public string $slug;
    public bool $active;
    public string $stockNumber;
    public string $articleNumber;
    public string|int|null $categoryId;
    public string|int|null $supplierId;
    public string|int|null $brandId;
    public string|int $unitId;
    public float|null $priceRetail;
    public int|null $minLimit;
    public string|null $notes;
    public bool $forShop;
    public bool $isNew;
    public bool $isPopular;
    public bool $isSale;
    public float|null $discount;
    public float|null $deliveryCost;
    public float|null $oldPrice;
    public float|null $length;
    public float|null $width;
    public float|null $height;
    public float|null $weight;
    public string|null $packageType;
    public float|null $minLimitPrice;

    public UploadedFile|null $imageMain;
    public array $images = [];
    public array $features = [];

    public SeoDto $seoDto;
    public null|PurchaseDto $purchaseDto;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = data_get($data, 'name');
        $self->slug = data_get($data, 'slug');
        $self->active = $data['active'] ?? true;
        $self->stockNumber = data_get($data, 'stock_number');
        $self->articleNumber = data_get($data, 'article_number');
        $self->categoryId = data_get($data, 'category_id');
        $self->supplierId = data_get($data, 'supplier_id');
        $self->brandId = data_get($data, 'brand_id');
        $self->unitId = data_get($data, 'unit_id');
        $self->priceRetail = data_get($data, 'price_retail');
        $self->minLimit = data_get($data, 'min_limit');
        $self->notes = data_get($data, 'notes');
        $self->forShop = $data['for_shop'] ?? false;
        $self->length = data_get($data, 'length');
        $self->width = data_get($data, 'width');
        $self->height = data_get($data, 'height');
        $self->weight = data_get($data, 'weight');
        $self->packageType = data_get($data, 'package_type');
        $self->minLimitPrice = data_get($data, 'min_limit_price');
        $self->isNew = $data['is_new'] ?? false;
        $self->isPopular = $data['is_popular'] ?? false;
        $self->isSale = $data['is_sale'] ?? false;
        $self->discount = data_get($data, 'discount');
        $self->oldPrice = data_get($data, 'old_price');
        $self->deliveryCost = $data['delivery_cost'] ?? null;

        $self->imageMain = $data[Inventory::MAIN_IMAGE_FIELD_NAME] ?? null;
        $self->images =  $data[Inventory::GALLERY_FIELD_NAME] ?? [];

        $self->seoDto = SeoDto::byArgs($data['seo'] ?? []);
        $self->purchaseDto = isset($data['purchase'])
            ? PurchaseDto::byArgs($data['purchase'])
            : null
        ;

        foreach ($data['features'] ?? [] as $item){
            $self->features[] = InventoryFeatureDto::byArgs($item);

        }

        return $self;
    }
}
