<?php

namespace App\Http\Resources\Suppliers;

use App\Models\Suppliers\Supplier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SupplierShortRaw", type="object", allOf={
 *     @OA\Schema(required={"id", "name"},
 *         @OA\Property(property="id", type="integer", description="Supplier id", example=1),
 *         @OA\Property(property="name", type="string", description="Supplier Name", example="LLC GM Company"),
 *         @OA\Property(property="url", type="string", description="Supplier url", example="http://gmcompany.com"),
 *         @OA\Property(property="contact", description="Supplier Contact data", type="object", allOf={
 *             @OA\Schema(required={"name", "phone", "email"},
 *                 @OA\Property(property="name", type="string", description="Supplier Contact Name", example="John McClane"),
 *                 @OA\Property(property="email", type="string", description="Supplier Contact email", example="jack@mail.com"),
 *                 @OA\Property(property="phone", type="string", description="Supplier Contact phone", example="15645665464"),
 *                 @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension", example="234"),
 *                 @OA\Property(property="position", type="string", description="Supplier Contact position", example="manager"),
 *             )
 *         }),
 *     )
 * })
 *
 * @OA\Schema(
 *     schema="SupplierShortListResource",
 *     @OA\Property(property="data", description="Supplier list", type="array",
 *         @OA\Items(ref="#/components/schemas/SupplierShortRaw")
 *     ),
 * )
 *
 * @mixin Supplier
 */
class SupplierShortListResource extends JsonResource
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
                    'phone' => $mainContact->phone->getValue(),
                    'phone_extension' => $mainContact->phone_extension,
                    'position' => $mainContact->position,
                ]
                : null,
        ];
    }
}

