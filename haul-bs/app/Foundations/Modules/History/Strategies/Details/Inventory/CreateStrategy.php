<?php

namespace App\Foundations\Modules\History\Strategies\Details\Inventory;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;

class CreateStrategy extends BaseDetailsStrategy
{
    public function __construct(protected Inventory $model)
    {}

    private function exclude(): array
    {
        return [
            'id',
            'active',
            'deleted_at',
            'updated_at',
            'created_at'
        ];
    }

    public function getDetails(): array
    {
        $attr = $this->model->getAttributes();

        foreach ($this->exclude() as $key){
            unset($attr[$key]);
        }

        $tmp = [];
        foreach ($attr as $k => $value){
            if($value === null) continue;

            if($k == 'unit_id'){
                $unit = Unit::query()
                    ->select('name')
                    ->where('id', $value)
                    ->toBase()
                    ->first();
                $tmp[$k] = [
                    'old' => null,
                    'new' => $unit->name,
                    'type' => self::TYPE_ADDED
                ];
            } elseif ($k == 'brand_id'){
                $brand = Brand::query()
                    ->select('name')
                    ->where('id', $value)
                    ->toBase()
                    ->first();
                $tmp[$k] = [
                    'old' => null,
                    'new' => $brand->name,
                    'type' => self::TYPE_ADDED
                ];
            } elseif ($k == 'category_id'){
                $category = Category::query()
                    ->select('name')
                    ->where('id', $value)
                    ->toBase()
                    ->first();
                $tmp[$k] = [
                    'old' => null,
                    'new' => $category->name,
                    'type' => self::TYPE_ADDED
                ];
            } elseif ($k == 'supplier_id'){
                $supplier = Supplier::query()
                    ->select('name')
                    ->where('id', $value)
                    ->toBase()
                    ->first();
                $tmp[$k] = [
                    'old' => null,
                    'new' => $supplier->name,
                    'type' => self::TYPE_ADDED
                ];
            } else {
                $tmp[$k] = [
                    'old' => null,
                    'new' => $value,
                    'type' => self::TYPE_ADDED
                ];
            }
        }

        if($media = $this->model->getMainImg()){
            /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */
            $tmp["{$media->collection_name}.{$media->id}.name"] = [
                'old' => null,
                'new' => $media->name,
                'type' => self::TYPE_ADDED
            ];
        }

        if(!empty($this->model->getGallery())){
            foreach ($this->model->getGallery() as $media) {
                /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */
                $tmp["{$media->collection_name}.{$media->id}.name"] = [
                    'old' => null,
                    'new' => $media->name,
                    'type' => self::TYPE_ADDED
                ];
            }
        }

        return $tmp;
    }
}
