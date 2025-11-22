<?php

namespace App\Repositories\Catalog\Product;

use App\Models\Catalog\Products\Product;
use App\Models\Companies\Price;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ProductRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Product::query();
    }

    public function getListForDealerOrder(
        Dealer $dealer,
        ?Order $order = null
    ): Collection {
        $itemsIds = [];

        if ($order) {
            $itemsIds =
                $order->items?->pluck('id', 'product_id')?->toArray() ?: [];
        }

        $company = $dealer->company;
        $prices = Price::query()->where('company_id', $company->id)->get();
        $productIds = $prices->pluck('price', 'product_id')->toArray();
        $desc = $prices->pluck('desc', 'product_id')->toArray();

        $tmp = collect();

        $products = Product::query()
            ->active()
            ->whereHas('brand')
            ->with(['brand', 'media', 'relationProducts'])
            ->whereIn('id', array_keys($productIds))
            ->get();

        foreach ($products as $p) {
            /** @var $p Product */
            if (empty($productIds[$p->id])) {
                continue;
            }

            $accessories = collect();
            if($p->relationProducts->isNotEmpty()){
                foreach ($p->relationProducts as $relationProduct){
                    /** @var $relationProduct Product */
                    if (empty($productIds[$relationProduct->id])) {
                        continue;
                    }
                    $accessories->push([
                        'id' => $relationProduct->id,
                        'title' => $relationProduct->title,
                        'slug' => $relationProduct->slug,
                        'price' => $productIds[$relationProduct->id],
                        'price_description' => $desc[$relationProduct->id] ?? null,
                        'owner_type' => $relationProduct->owner_type,
                        'category_id' => $relationProduct->category_id,
                        'brand' => $relationProduct->brand?->name,
                        'img' => $relationProduct->getImgUrl(),
                        'is_added' => !empty($itemsIds)
                            && array_key_exists(
                                $relationProduct->id,
                                $itemsIds
                            )
                    ]);
                }
            }

            $tmp->push([
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'price' => $productIds[$p->id],
                'price_description' => $desc[$p->id] ?? null,
                'owner_type' => $p->owner_type,
                'category_id' => $p->category_id,
                'brand' => $p->brand?->name,
                'img' => $p->getImgUrl(),
                'is_added' => !empty($itemsIds)
                    && array_key_exists(
                        $p->id,
                        $itemsIds
                    ),
                'accessories' => $accessories
            ]);
        }

        return $tmp;
    }

    public function unitsSearch(
        array $serialNumbers,
        array $select = ['*'],
        array $relations = []
    ): Collection
    {
        $tmp = collect();
        foreach ($serialNumbers as $sn){
            $item = $this->unitSearch($sn, $select, $relations);
            if($item){
                $tmp->push($item);
            }
        }

        return $tmp;
    }

    public function unitSearch(
        string $serialNumber,
        array $select = ['id'],
        array $relations = []
    ): ?Product
    {
        $model = Product::query()
            ->select($select)
            ->where('active', true)
            ->whereHas('serialNumbers',
                static fn(Builder $b) => $b->where('serial_number', $serialNumber)
            )
            ->with($relations)
            ->first();

        if ($model) {
            $model->serial_number = $serialNumber;
        }

        return $model;
    }
}
