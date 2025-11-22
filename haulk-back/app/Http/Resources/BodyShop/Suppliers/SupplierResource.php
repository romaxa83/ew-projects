<?php

namespace App\Http\Resources\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Supplier
 */
class SupplierResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="Supplier",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Supplier data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name"},
     *                     @OA\Property(property="id", type="integer", description="Supplier id"),
     *                     @OA\Property(property="name", type="string", description="Supplier Name"),
     *                     @OA\Property(property="url", type="string", description="Supplier url"),
     *                     @OA\Property(
     *                         property="contacts",
     *                         description="Supplier Contacts data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/SupplierContact")
     *                     ),
     *                     @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Supplier can be deleted"),
     *                 )
     *             }
     *         ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'contacts' => SupplierContactResource::collection($this->contacts),
            'hasRelatedEntities' => $this->hasRelatedEntities(),
        ];
    }
}
