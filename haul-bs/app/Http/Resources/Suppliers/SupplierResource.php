<?php

namespace App\Http\Resources\Suppliers;

use App\Models\Suppliers\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SupplierRaw", type="object", allOf={
 *     @OA\Schema(required={"id", "name"},
 *         @OA\Property(property="id", type="integer", description="Supplier id", example=1),
 *         @OA\Property(property="name", type="string", description="Supplier Name", example="LLC GM Company"),
 *         @OA\Property(property="url", type="string", description="Supplier url", example="http://gmcompany.com"),
 *         @OA\Property(property="hasRelatedEntities", type="boolean", description="Is Supplier can be deleted"),
 *         @OA\Property(property="contact", description="Supplier Contact data", type="object", allOf={
 *             @OA\Schema(required={"name", "phone", "email"},
 *                 @OA\Property(property="name", type="string", description="Supplier Contact Name", example="John McClane"),
 *                 @OA\Property(property="email", type="string", description="Supplier Contact email", example="jack@mail.com"),
 *                 @OA\Property(property="emails", type="array", description="Supplier Contact aditional emails",
 *                     @OA\Items(allOf={
 *                         @OA\Schema(@OA\Property(property="value", type="string",),)
 *                     }
 *                 )
 *                 ),
 *                 @OA\Property(property="phone", type="string", description="Supplier Contact phone", example="15645665464"),
 *                 @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension", example="234"),
 *                 @OA\Property(property="phones", type="array", description="Supplier Contact aditional phones",
 *                     @OA\Items(ref="#/components/schemas/PhonesRaw")
 *                 ),
 *                 @OA\Property(property="position", type="string", description="Supplier Contact position", example="manager"),
 *             )
 *         }),
 *     )
 * })
 *
 * @OA\Schema(
 *     schema="SupplierPaginationResource",
 *     @OA\Property(property="data", description="Supplier paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/SupplierRaw")
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
 * )
 *
 * @mixin Supplier
 */
class SupplierResource extends JsonResource
{

    public function toArray($request)
    {
        $mainContact = $this->mainContact();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url ?? '',
            'contact' => $mainContact
                ? [
                    'name' => $mainContact->name,
                    'email' => $mainContact->email->getValue(),
                    'emails' => $mainContact->emails,
                    'phone' => $mainContact->phone->getValue(),
                    'phone_extension' => $mainContact->phone_extension,
                    'phones' => $mainContact->phones,
                    'position' => $mainContact->position,
                ]
                : null,
            'hasRelatedEntities' => $this->hasRelatedEntities(),
        ];
    }
}
