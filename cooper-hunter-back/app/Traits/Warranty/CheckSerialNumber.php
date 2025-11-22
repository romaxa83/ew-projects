<?php

namespace App\Traits\Warranty;

use App\Models\Catalog\Products\Product;
use App\Models\Technicians\Technician;
use App\Repositories\Catalog\Product\ProductRepository;
use Core\Exceptions\TranslatedException;
use Illuminate\Support\Facades\Auth;

trait CheckSerialNumber
{
    protected function assetBySystemUnits(array $units = []): void
    {
        if(!empty($units)){
            $productIds = array_column($units, 'product_id');
            /** @var $productRepo ProductRepository */
            $productRepo = resolve(ProductRepository::class);
            $products = $productRepo->getAllWhereIn(['id' => $productIds], ['unitType']);

            $monoblockID = 0;
            $outdoorID = 0;
            $products->each(function(Product $item) use (&$monoblockID, &$outdoorID) {
                if($item->unitType && $item->unitType->isMonoblock()){
                    $monoblockID = $item->id;
                }
                if($item->unitType && $item->unitType->isOutdoor()){
                    $outdoorID = $item->id;
                }
            });

            // если есть моноблок, то серийник должен быть один
            if($monoblockID && $products->count() > 1){
                throw new TranslatedException(__('exceptions.commercial.warranty.monoblock_must_be_one'), 502);
            }

            if($outdoorID){
                /** @var $outdoor Product */
                $outdoor = $products->where('id', $outdoorID)->first();
                if(null === $outdoor->unit_sub_type){
                    throw new TranslatedException(__('exceptions.commercial.warranty.outdoor_has_no_sub_type'), 502);
                }
                if($outdoor->unit_sub_type->isSingle()){
                    if($products->count() != 2){
                        throw new TranslatedException(__('exceptions.commercial.warranty.outdoor_single_has_more_indoor'), 502);
                    }
                    /** @var $indoor Product */
                    $indoor = $products->where('id', '!=', $outdoorID)->first();
                    if(!$indoor->unitType || !$indoor->unitType->isIndoor()){
                        throw new TranslatedException(__('exceptions.commercial.warranty.outdoor_single_has_more_indoor'), 502);
                    }
                }
                if($outdoor->unit_sub_type->isMulti()){
                    if($products->count() < 3 || $products->count() > 6){
                        throw new TranslatedException(__('exceptions.commercial.warranty.outdoor_multi_consist_indoor'), 502);
                    }
                    $notIndoor = false;
                    $products->each(function(Product $item) use (&$notIndoor, $outdoorID) {
                        if($item->id != $outdoorID){
                            if($item->unitType && !$item->unitType->isIndoor()){
                                $notIndoor = true;
                            }
                        }
                    });
                    if($notIndoor){
                        throw new TranslatedException(__('exceptions.commercial.warranty.outdoor_multi_consist_not_indoor'), 502);
                    }
                }
            }

            // не передан моноблок или аутдор
            if(!$outdoorID && !$monoblockID){
                throw new TranslatedException(__('exceptions.commercial.warranty.must_be_monoblock_or_outdoor'), 502);
            }
        }
    }
}
