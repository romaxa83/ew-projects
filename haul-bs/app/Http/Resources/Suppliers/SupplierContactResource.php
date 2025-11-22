<?php

namespace App\Http\Resources\Suppliers;

use App\Models\Suppliers\SupplierContact;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="SupplierContactRaw", type="object", allOf={
 *     @OA\Schema(required={"id", "name", "phone", "email"},
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="LLC GM Company"),
 *         @OA\Property(property="email", type="string", example="jack@mail.com"),
 *         @OA\Property(property="emails", type="array", description="Supplier Contact aditional emails",
 *             @OA\Items(allOf={
 *                 @OA\Schema(@OA\Property(property="value", type="string",),)
 *                  }
 *         )),
 *         @OA\Property(property="phone", type="string", description="Supplier Contact phone", example="15645665464"),
 *         @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension", example="234"),
 *         @OA\Property(property="phones", type="array", description="Supplier Contact aditional phones",
 *             @OA\Items(ref="#/components/schemas/PhonesRaw")
 *         ),
 *         @OA\Property(property="position", type="string", description="Supplier Contact position", example="manager"),
 *         @OA\Property(property="is_main", type="boolean", example=true),
 *
 *     )
 * })
 *
 * @OA\Schema(
 *     schema="SupplierContactResource",
 *     @OA\Property(property="data", description="Supplier paginated list", type="array",
 *         @OA\Items(ref="#/components/schemas/SupplierContactRaw")
 *     ),
 * )
 *
 * @mixin SupplierContact
 */
class SupplierContactResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email->getValue(),
            'emails' => $this->emails,
            'phone' => $this->phone->getValue(),
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'position' => $this->position,
            'is_main' => $this->is_main,
        ];
    }
}
