<?php

namespace App\Http\Resources\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Inventory
 */
class InventoryResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="Inventory",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Inventory data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name", "stock_number", "price_wholesale", "quantity"},
     *                     @OA\Property(property="id", type="integer", description="Inventory id"),
     *                     @OA\Property(property="name", type="string", description="Inventory Name"),
     *                     @OA\Property(property="stock_number", type="string", description="Inventory Stock number"),
     *                     @OA\Property(property="price_retail", type="number", description="Inventory retail price"),
     *                     @OA\Property(property="quantity", type="number", description="Inventory quantity"),
     *                     @OA\Property(property="min_limit", type="number", description="Inventory min linit"),
     *                     @OA\Property(property="for_sale", type="boolean", description="For sale"),
     *                     @OA\Property(property="status", type="string", description="Inventory status"),
     *                     @OA\Property(property="category_id", type="integer", description="Inventory category"),
     *                     @OA\Property(
     *                         property="category",
     *                         description="Inventory Category data",
     *                         type="object",
     *                         allOf={
     *                             @OA\Schema(
     *                                 required={"id", "name"},
     *                                 @OA\Property(property="name", type="string", description="Category Name"),
     *                                 @OA\Property(property="id", type="integer", description="Category id")
     *                             )
     *                         }
     *                     ),
     *                     @OA\Property(property="supplier_id", type="integer", description="Inventory supplier"),
     *                     @OA\Property(
     *                         property="supplier",
     *                         description="Inventory Supplier data",
     *                         allOf={
     *                             @OA\Schema(
     *                                 required={"id", "name"},
     *                                 @OA\Property(property="id", type="integer", description="Supplier id"),
     *                                 @OA\Property(property="name", type="string", description="Supplier Name"),
     *                                 @OA\Property(property="url", type="string", description="Supplier url"),
     *                                 @OA\Property(
     *                                     property="contact",
     *                                     description="Supplier Contact data",
     *                                     type="object",
     *                                     allOf={
     *                                         @OA\Schema(
     *                                             required={"name", "phone", "email"},
     *                                             @OA\Property(property="name", type="string", description="Supplier Contact Name"),
     *                                             @OA\Property(property="email", type="string", description="Supplier Contact email"),
     *                                             @OA\Property(property="phone", type="string", description="Supplier Contact phone"),
     *                                             @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension"),
     *                                             @OA\Property(property="position", type="string", description="Supplier Contact position"),
     *                                         )
     *                                     }
     *                                 ),
     *                             )
     *                         }
     *                     ),
     *                     @OA\Property(property="notes", type="string", description="Inventory notes"),
     *                     @OA\Property(property="unit_id_id", type="integer", description="Inventory unit"),
     *                     @OA\Property(
     *                         property="unit",
     *                         description="Inventory Unit data",
     *                         type="object",
     *                         allOf={
     *                             @OA\Schema(
     *                                 required={"id", "name", "accept_decimals"},
     *                                 @OA\Property(property="name", type="string", description="Unit Name"),
     *                                 @OA\Property(property="id", type="integer", description="Unit id"),
     *                                 @OA\Property(property="accept_decimals", type="boolean", description="Unit accept decimals"),
     *                             )
     *                         }
     *                     ),
     *                     @OA\Property(property="hasRelatedOpenOrders", type="boolean", description="Is inventory has related open orders"),
     *                     @OA\Property(property="hasRelatedDeletedOrders", type="boolean", description="Is inventory has related deleted orders"),
     *                     @OA\Property(property="hasRelatedTypesOfWork", type="boolean", description="Is inventory has related types of work"),
     *                     @OA\Property(property="length", type="number"),
     *                     @OA\Property(property="width", type="number"),
     *                     @OA\Property(property="height", type="number"),
     *                     @OA\Property(property="weight", type="number"),
     *                     @OA\Property(property="min_limit_price", type="number"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        $mainContact = $this->supplier ? $this->supplier->mainContact() : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_number' => $this->stock_number,
            'price_retail' => $this->price_retail,
            'quantity' => $this->quantity,
            'min_limit' => $this->min_limit,
            'for_sale' => $this->for_sale,
            'status' => $this->getStatus(),
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
                    'url' => $this->supplier->url ?? null,
                    'contact' => $mainContact
                        ? [
                            'name' => $mainContact->name,
                            'email' => $mainContact->email,
                            'phone' => $mainContact->phone,
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
            'min_limit_price' => $this->min_limit_price,
        ];
    }
}
