<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductSimpleResourceCollection extends ResourceCollection
{
    protected array $collectionData = [];

    public function setCollectionData(array $collection) {
        $this->collectionData = $collection;
        return $this;
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->each->setCollectionData($this->collectionData);
        return parent::toArray($request);
    }
}
