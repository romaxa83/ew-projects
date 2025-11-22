<?php

namespace App\Foundations\Modules\History\Strategies\Details\Inventory;

use App\Foundations\Modules\History\Strategies\Details\BaseDetailsStrategy;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Unit;
use App\Models\Suppliers\Supplier;

class UpdateStrategy extends BaseDetailsStrategy
{
    public function __construct(
        protected Inventory $model,
        protected array $additional = [],
    )
    {}

    private function exclude(): array
    {
        return [
            'updated_at'
        ];
    }

    public function getDetails(): array
    {
        $attr = $this->model->getChanges();

        foreach ($this->exclude() as $key){
            unset($attr[$key]);
        }

        $tmp = [];

        if(isset($this->additional)){
            foreach ($attr as $k => $value){
                if($k == 'brand_id'){
                    $brand = Brand::query()
                        ->select('name')
                        ->where('id', $value)
                        ->toBase()
                        ->first();
                    $brandOld = Brand::query()
                        ->select('name')
                        ->where('id', $this->additional[$k])
                        ->toBase()
                        ->first();

                    $tmp[$k] = [
                        'old' => $brandOld?->name,
                        'new' => $brand?->name,
                        'type' => self::TYPE_UPDATED
                    ];
                } elseif ($k == 'category_id'){
                    $category = Category::query()
                        ->select('name')
                        ->where('id', $value)
                        ->toBase()
                        ->first();
                    $categoryOld = Category::query()
                        ->select('name')
                        ->where('id', $this->additional[$k])
                        ->toBase()
                        ->first();

                    $tmp[$k] = [
                        'old' => $categoryOld?->name,
                        'new' => $category?->name,
                        'type' => self::TYPE_UPDATED
                    ];
                } elseif ($k == 'unit_id'){
                    $unit = Unit::query()
                        ->select('name')
                        ->where('id', $value)
                        ->toBase()
                        ->first();
                    $unitOld = Unit::query()
                        ->select('name')
                        ->where('id', $this->additional[$k])
                        ->toBase()
                        ->first();

                    $tmp[$k] = [
                        'old' => $unitOld?->name,
                        'new' => $unit?->name,
                        'type' => self::TYPE_UPDATED
                    ];
                } elseif ($k == 'supplier_id') {
                    $supplier = Supplier::query()
                        ->select('name')
                        ->where('id', $value)
                        ->toBase()
                        ->first();
                    $supplierOld = Supplier::query()
                        ->select('name')
                        ->where('id', $this->additional[$k])
                        ->toBase()
                        ->first();

                    $tmp[$k] = [
                        'old' => $supplierOld?->name,
                        'new' => $supplier?->name,
                        'type' => self::TYPE_UPDATED
                    ];
                } else {
                    $tmp[$k] = [
                        'old' => $this->additional[$k],
                        'new' => $value,
                        'type' => self::TYPE_UPDATED
                    ];
                }
            }

            foreach ($this->model->media as $media){
                /** @var $media \Spatie\MediaLibrary\MediaCollections\Models\Media */

                if(
                    isset($this->additional['media'])
                    && (
                        $this->additional['media']->isEmpty()
                        || $this->additional['media']->contains(fn($i) => $i->id != $media->id)
                    )
                ){
                    $tmp["{$media->collection_name}.{$media->id}.name"] = [
                        'old' => null,
                        'new' => $media->name,
                        'type' => self::TYPE_ADDED
                    ];
                }
            }
        }

        return $tmp;
    }
}
