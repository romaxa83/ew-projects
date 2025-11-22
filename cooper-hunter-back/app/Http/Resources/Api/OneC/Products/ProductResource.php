<?php

namespace App\Http\Resources\Api\OneC\Products;

use App\Http\Resources\Api\OneC\Catalog\Certificates\CertificateResource;
use App\Models\Catalog\Products\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guid' => $this->guid,
            'category_id' => $this->category->id,
            'category_guid' => $this->category->guid,
            'seer' => $this->seer,
            'title' => $this->title,
            'certificates' => CertificateResource::collection($this->certificates),
            'translations' => ProductTranslationResource::collection($this->translations),
        ];
    }
}
