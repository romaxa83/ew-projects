<?php

namespace App\Http\Resources\Inventories\Inventory;

use App\Foundations\Modules\Seo\Deliveries\Http\Resources\SeoResource;
use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Inventories\Feature\FeatureECommForInventoryResource;
use App\Models\Inventories\Inventory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryECommRaw", type="object",
 *     @OA\Property(property="data", type="object", description="Inventory data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "stock_number", "slug", "quantity"},
 *             @OA\Property(property="id", type="integer", description="Inventory id"),
 *             @OA\Property(property="category_id", type="integer", description="Inventory category"),
 *             @OA\Property(property="unit", description="Inventory Unit data", type="object", allOf={
 *                 @OA\Schema(
 *                     required={"id", "name", "accept_decimals"},
 *                     @OA\Property(property="name", type="string", description="Unit Name"),
 *                     @OA\Property(property="id", type="integer", description="Unit id"),
 *                     @OA\Property(property="accept_decimals", type="boolean", description="Unit accept decimals"),
 *                 )}
 *             ),
 *             @OA\Property(property="supplier_id", type="integer", description="Inventory supplier"),
 *             @OA\Property(property="brand_id", type="integer", description="Inventory brand"),
 *             @OA\Property(property="active", type="boolean"),
 *             @OA\Property(property="name", type="string", description="Inventory Name"),
 *             @OA\Property(property="slug", type="string", description="Inventory slug"),
 *             @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
 *             @OA\Property(property="article_number", type="string", description="Inventory article number"),
 *             @OA\Property(property="price_retail", type="number", description="Inventory retail price"),
 *             @OA\Property(property="min_limit_price", type="number"),
 *             @OA\Property(property="quantity", type="number", description="Inventory quantity"),
 *             @OA\Property(property="min_limit", type="number", description="Inventory min linit"),
 *             @OA\Property(property="for_shop", type="boolean", description="For shop"),
 *             @OA\Property(property="notes", type="string", description="Inventory notes"),
 *             @OA\Property(property="length", type="number"),
 *             @OA\Property(property="width", type="number"),
 *             @OA\Property(property="height", type="number"),
 *             @OA\Property(property="weight", type="number"),
 *             @OA\Property(property="package_type", type="string", enum={"custom_package", "carrier_package"}),
 *             @OA\Property(property="is_new", type="boolean"),
 *             @OA\Property(property="is_popular", type="boolean"),
 *             @OA\Property(property="is_sale", type="boolean"),
 *             @OA\Property(property="old_price", type="number"),
 *             @OA\Property(property="discount", type="number"),
 *             @OA\Property(property="delivery_cost", type="number"),
 *             @OA\Property(property="created_at", type="integer"),
 *             @OA\Property(property="updated_at", type="integer"),
 *             @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoResource"),
 *             @OA\Property(property="features", type="array", description="Features relation for inventory",
 *                 @OA\Items(ref="#/components/schemas/FeatureECommForInventoryRaw")
 *             ),
 *             @OA\Property(property="main_image", type="object", description="Main inventory image", allOf={
 *                 @OA\Schema(ref="#/components/schemas/Image")
 *             }),
 *             @OA\Property(property="gallery", type="array", description="Gallery inventory images",
 *                  @OA\Items(ref="#/components/schemas/Image")
 *             ),
 *         )}
 *     ),
 * )
 *
 * @OA\Schema(schema="InventoryECommResource",
 *     @OA\Property(property="data", description="Inventory list", type="array",
 *         @OA\Items(ref="#/components/schemas/InventoryECommRaw")
 *     ),
 * )
 *
 * @mixin Inventory
 */

class InventoryECommResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'unit' => [
                'id' =>  $this->unit->id,
                'name' =>  $this->unit->name,
                'accept_decimals' =>  $this->unit->accept_decimals,
            ],
            'supplier_id' => $this->supplier_id,
            'brand_id' => $this->brand_id,
            'active' => $this->active,
            'name' => $this->name,
            'slug' => $this->slug,
            'stock_number' => $this->stock_number,
            'price_retail' => $this->price_retail,
            'min_limit_price' => $this->min_limit_price,
            'quantity' => $this->quantity,
            'min_limit' => $this->min_limit,
            'notes' => $this->notes,
            'for_shop' => $this->for_shop,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'weight' => $this->weight,
            'is_new' => $this->is_new,
            'is_popular' => $this->is_popular,
            'is_sale' => $this->is_sale,
            'package_type' => $this->package_type?->value,
            'old_price' => $this->old_price,
            'discount' => $this->discount,
            'delivery_cost' => $this->delivery_cost,
            'article_number' => $this->article_number,
            Inventory::MAIN_IMAGE_FIELD_NAME => ImageResource::make($this->getMainImg()),
            Inventory::GALLERY_FIELD_NAME => ImageResource::collection($this->getGallery()),
            'seo' => SeoResource::make($this->seo),
            'features' => FeatureECommForInventoryResource::collection($this->getFeaturesWithValues()),
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
        ];
    }
}
