<?php

namespace App\Http\Resources\Inventories\Inventory;

use App\Foundations\Modules\Seo\Deliveries\Http\Resources\SeoResource;
use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Inventories\Brand\BrandShortListResource;
use App\Http\Resources\Inventories\Feature\FeatureForInventoryResource;
use App\Models\Inventories\Inventory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="InventoryResource", type="object",
 *     @OA\Property(property="data", type="object", description="Inventory data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "stock_number", "slug", "quantity"},
 *             @OA\Property(property="id", type="integer", description="Inventory id"),
 *             @OA\Property(property="name", type="string", description="Inventory Name"),
 *             @OA\Property(property="slug", type="string", description="Inventory slug"),
 *             @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
 *             @OA\Property(property="article_number", type="string", description="Inventory article number"),
 *             @OA\Property(property="price_retail", type="number", description="Inventory retail price"),
 *             @OA\Property(property="quantity", type="number", description="Inventory quantity"),
 *             @OA\Property(property="min_limit", type="number", description="Inventory min linit"),
 *             @OA\Property(property="for_shop", type="boolean", description="For shop"),
 *             @OA\Property(property="status", type="string", description="Inventory status"),
 *             @OA\Property(property="category_id", type="integer", description="Inventory category"),
 *             @OA\Property(property="category", description="Inventory Category data", type="object", allOf={
 *                 @OA\Schema(
 *                     required={"id", "name"},
 *                     @OA\Property(property="name", type="string", description="Category Name"),
 *                     @OA\Property(property="id", type="integer", description="Category id")
 *                 )}
 *             ),
 *             @OA\Property(property="supplier_id", type="integer", description="Inventory supplier"),
 *             @OA\Property(property="supplier", description="Inventory Supplier data", allOf={
 *                 @OA\Schema(
 *                     required={"id", "name"},
 *                     @OA\Property(property="id", type="integer", description="Supplier id"),
 *                     @OA\Property(property="name", type="string", description="Supplier Name"),
 *                     @OA\Property(property="url", type="string", description="Supplier url"),
 *                     @OA\Property(property="contact", description="Supplier Contact data", type="object", allOf={
 *                         @OA\Schema(
 *                             required={"name", "phone", "email"},
 *                             @OA\Property(property="name", type="string", description="Supplier Contact Name"),
 *                             @OA\Property(property="email", type="string", description="Supplier Contact email"),
 *                             @OA\Property(property="phone", type="string", description="Supplier Contact phone"),
 *                             @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension"),
 *                             @OA\Property(property="position", type="string", description="Supplier Contact position"),
 *                         )}
 *                     ),
 *                 )}
 *             ),
 *             @OA\Property(property="notes", type="string", description="Inventory notes"),
 *             @OA\Property(property="unit_id", type="integer", description="Inventory unit"),
 *             @OA\Property(property="unit", description="Inventory Unit data", type="object", allOf={
 *                 @OA\Schema(
 *                     required={"id", "name", "accept_decimals"},
 *                     @OA\Property(property="name", type="string", description="Unit Name"),
 *                     @OA\Property(property="id", type="integer", description="Unit id"),
 *                     @OA\Property(property="accept_decimals", type="boolean", description="Unit accept decimals"),
 *                 )}
 *             ),
 *             @OA\Property(property="brand", type="object", ref="#/components/schemas/InventoryBrandRawShort"),
 *             @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is inventory has related open orders"),
 *             @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is inventory has related deleted orders"),
 *             @OA\Property(property="hasRelatedTypesOfWork", type="boolean", description="Is inventory has related types of work"),
 *             @OA\Property(property="length", type="number"),
 *             @OA\Property(property="width", type="number"),
 *             @OA\Property(property="height", type="number"),
 *             @OA\Property(property="weight", type="number"),
 *             @OA\Property(property="package_type", type="string", enum={"custom_package", "carrier_package"}),
 *             @OA\Property(property="min_limit_price", type="number"),
 *             @OA\Property(property="is_new", type="boolean"),
 *             @OA\Property(property="is_popular", type="boolean"),
 *             @OA\Property(property="is_sale", type="boolean"),
 *             @OA\Property(property="old_price", type="number"),
 *             @OA\Property(property="discount", type="number"),
 *             @OA\Property(property="delivery_cost", type="number"),
 *             @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoResource"),
 *             @OA\Property(property="features", type="array", description="Features relation for inventory",
 *                 @OA\Items(ref="#/components/schemas/InventoryFeatureRawResource")
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
 * @mixin Inventory
 */
class InventoryResource extends JsonResource
{

    public function toArray($request)
    {
        $mainContact = $this->supplier ? $this->supplier->mainContact() : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'stock_number' => $this->stock_number,
            'article_number' => $this->article_number,
            'price_retail' => $this->price_retail,
            'quantity' => $this->quantity,
            'min_limit' => $this->min_limit,
            'for_shop' => $this->for_shop,
            'status' => $this->getStatus(),
            'brand' => BrandShortListResource::make($this->brand),
            'category_id' => $this->category_id,
            'category' => $this->category
                ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ] : null,
            'supplier' => $this->supplier
                ? [
                    'id' => $this->supplier->id ?? null,
                    'name' => $this->supplier->name ?? null,
                    'url' => $this->supplier->url ?? '',
                    'contact' => $mainContact
                        ? [
                            'name' => $mainContact->name,
                            'email' => $mainContact->email->getValue(),
                            'phone' => $mainContact->phone->getValue(),
                            'phone_extension' => $mainContact->phone_extension,
                            'position' => $mainContact->position,
                        ]
                        : null,
                ] : null,
            'supplier_id' => $this->supplier_id,
            'notes' => $this->notes,
            'unit_id' => $this->unit_id,
            'unit' => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'accept_decimals' => $this->unit->accept_decimals,
            ],
            'hasRelatedOpenOrders' => $this->hasRelatedOpenOrders(),
            'hasRelatedDeletedOrders' => $this->hasRelatedDeletedOrders(),
            'hasRelatedTypesOfWork' => $this->hasRelatedTypesOfWork(),
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'weight' => $this->weight,
            'package_type' => $this->package_type?->value,
            'min_limit_price' => $this->min_limit_price,
            'is_new' => $this->is_new,
            'is_popular' => $this->is_popular,
            'is_sale' => $this->is_sale,
            'old_price' => $this->old_price,
            'discount' => $this->discount,
            'delivery_cost' => $this->delivery_cost,
            Inventory::MAIN_IMAGE_FIELD_NAME => ImageResource::make($this->getMainImg()),
            Inventory::GALLERY_FIELD_NAME => ImageResource::collection($this->getGallery()),
            'seo' => SeoResource::make($this->seo),
            'features' => FeatureForInventoryResource::collection($this->getFeaturesWithValues())
        ];
    }
}
