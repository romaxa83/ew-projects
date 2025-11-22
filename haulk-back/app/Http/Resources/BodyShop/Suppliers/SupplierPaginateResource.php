<?php

namespace App\Http\Resources\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Supplier
 */
class SupplierPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="SupplierRaw",
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
     *                     @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Supplier can be deleted"),
     *                     @OA\Property(
     *                         property="contact",
     *                         description="Supplier Contact data",
     *                         type="object",
     *                         allOf={
     *                             @OA\Schema(
     *                                 required={"name", "phone", "email"},
     *                                 @OA\Property(property="name", type="string", description="Supplier Contact Name"),
     *                                 @OA\Property(property="email", type="string", description="Supplier Contact email"),
     *                                 @OA\Property(property="emails", type="array", description="Supplier Contact aditional emails",
     *                                     @OA\Items(
     *                                         allOf={
     *                                             @OA\Schema(
     *                                                 @OA\Property(property="value", type="string",),
     *                                             )
     *                                         }
     *                                     )
     *                                 ),
     *                                 @OA\Property(property="phone", type="string", description="Supplier Contact phone"),
     *                                 @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension"),
     *                                 @OA\Property(property="phones", type="array", description="Supplier Contact aditional phones",
     *                                     @OA\Items(ref="#/components/schemas/PhonesRaw")
     *                                 ),
     *                                 @OA\Property(property="position", type="string", description="Supplier Contact position"),
     *                             )
     *                         }
     *                     ),
     *                 )
     *             }
     *         ),
     * )
     *
     * @OA\Schema(
     *     schema="SupplierPaginate",
     *     @OA\Property(
     *         property="data",
     *         description="Supplier paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/SupplierRaw")
     *     ),
     *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        $mainContact = $this->mainContact();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'contact' => $mainContact
                ? [
                    'name' => $mainContact->name,
                    'email' => $mainContact->email,
                    'emails' => $mainContact->emails,
                    'phone' => $mainContact->phone,
                    'phone_extension' => $mainContact->phone_extension,
                    'phones' => $mainContact->phones,
                    'position' => $mainContact->position,
                ]
                : null,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
        ];
    }
}
