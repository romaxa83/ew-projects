<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;
class BaseProductResource extends JsonResource
{
    protected array $collectionData = [];

    public function setCollectionData(array $collection) {
        $this->collectionData = $collection;
        return $this;
    }

    protected function getCollectionData()
    {
        $productID = $this->id;
        if(empty($this->collectionData)){
            $userID = Auth::user()?->id;

            if($userID){
                $item = DB::table('user_wishlist')
                    ->where('user_id', $userID)
                    ->where('product_id', $productID)
                    ->first();

                if($item?->collection_id !== null) {
                    $collection = Collection::query()
                        ->where('published', true)
                        ->where('id', $item->collection_id)
                        ->first();
                    if($collection){
                        $this->collectionData = $this->formatCollectionDataForProduct($collection);
                    }
                } else {
                    $this->collectionData = $this->formatCollectionDataForProduct($this->getFirstActiveCollection());
                }
            } else {
                $this->collectionData = $this->formatCollectionDataForProduct($this->getFirstActiveCollection());
            }
        }

        return (object)$this->collectionData;

    }

    public static function collection($resource)
    {
        return new ProductSimpleResourceCollection($resource);
    }
}

