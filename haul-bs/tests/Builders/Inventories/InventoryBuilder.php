<?php

namespace Tests\Builders\Inventories;

use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;
use App\Services\Inventories\InventoryService;
use Illuminate\Http\UploadedFile;
use Tests\Builders\BaseBuilder;

class InventoryBuilder extends BaseBuilder
{
    protected $imgMain = null;
    protected array $images = [];

    function modelClass(): string
    {
        return Inventory::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function slug(string $value): self
    {
        $this->data['slug'] = $value;
        return $this;
    }

    public function stock_number(string $value): self
    {
        $this->data['stock_number'] = $value;
        return $this;
    }

    public function article_number(string $value): self
    {
        $this->data['article_number'] = $value;
        return $this;
    }

    public function quantity(float $value): self
    {
        $this->data['quantity'] = $value;
        return $this;
    }

    public function length(?float $value): self
    {
        $this->data['length'] = $value;
        return $this;
    }

    public function width(?float $value): self
    {
        $this->data['width'] = $value;
        return $this;
    }

    public function height(?float $value): self
    {
        $this->data['height'] = $value;
        return $this;
    }

    public function weight(?float $value): self
    {
        $this->data['weight'] = $value;
        return $this;
    }

    public function package_type(?string $value): self
    {
        $this->data['package_type'] = $value;
        return $this;
    }

    public function for_shop(bool $value): self
    {
        $this->data['for_shop'] = $value;
        return $this;
    }

    public function min_limit(int $value): self
    {
        $this->data['min_limit'] = $value;
        return $this;
    }

    public function min_limit_price(int|float|null $value): self
    {
        $this->data['min_limit_price'] = $value;
        return $this;
    }

    public function old_price(float $value): self
    {
        $this->data['old_price'] = $value;
        return $this;
    }

    public function price_retail(float $value): self
    {
        $this->data['price_retail'] = $value;
        return $this;
    }

    public function delivery_cost(float $value): self
    {
        $this->data['delivery_cost'] = $value;
        return $this;
    }

    public function discount(int $value): self
    {
        $this->data['discount'] = $value;
        return $this;
    }

    public function supplier(Supplier|null $model): self
    {
        $this->data['supplier_id'] = $model->id;
        return $this;
    }

    public function unit(Unit $model): self
    {
        $this->data['unit_id'] = $model->id;
        return $this;
    }

    public function category(Category|null $model): self
    {
        if($model){
            $this->data['category_id'] = $model->id;
        } else {
            $this->data['category_id'] = $model;
        }
        return $this;
    }

    public function brand(Brand|null $model): self
    {
        if($model){
            $this->data['brand_id'] = $model->id;
        } else {
            $this->data['brand_id'] = $model;
        }

        return $this;
    }

    public function mainImg(UploadedFile $file): self
    {
        $this->imgMain = $file;
        return $this;
    }

    public function gallery(UploadedFile ...$models): self
    {
        $this->images = $models;
        return $this;
    }

    protected function afterSave($model): void
    {
        if($this->imgMain){
            /** @var $service InventoryService */
            $service = resolve(InventoryService::class);
            $service->uploadImage($model, $this->imgMain, Inventory::MAIN_IMAGE_FIELD_NAME);
        }
        if(!empty($this->images)){
            /** @var $service InventoryService */
            $service = resolve(InventoryService::class);
            $service->uploadImages($model, $this->images, Inventory::GALLERY_FIELD_NAME);
        }
    }

    protected function afterClear(): void
    {
        $this->images = [];
        $this->imgMain = null;
    }
}

