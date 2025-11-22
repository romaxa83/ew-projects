<?php

namespace App\Http\Resources\Inventories\Category;

use App\Foundations\Modules\Seo\Deliveries\Http\Resources\SeoResource;
use App\Http\Resources\Files\ImageResource;
use App\Models\Inventories\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array
 */

class CategoryTreeForSelectResource extends JsonResource
{
    public function toArray($request)
    {

        return $request;
    }
}
