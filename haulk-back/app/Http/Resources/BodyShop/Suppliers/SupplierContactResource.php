<?php

namespace App\Http\Resources\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\SupplierContact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupplierContact
 */
class SupplierContactResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="SupplierContact",
     *     type="object",
     *         @OA\Property(
     *             property="data",
     *             type="object",
     *             description="Supplier Contact data",
     *             allOf={
     *                 @OA\Schema(
     *                     required={"id", "name", "phone", "email"},
     *                     @OA\Property(property="id", type="integer", description="Supplier Contact id"),
     *                     @OA\Property(property="name", type="string", description="Supplier Contact Name"),
     *                     @OA\Property(property="email", type="string", description="Supplier Contact email"),
     *                     @OA\Property(property="emails", type="array", description="Supplier Contact aditional emails",
     *                         @OA\Items(
     *                             allOf={
     *                                 @OA\Schema(
     *                                     @OA\Property(property="value", type="string",),
     *                                 )
     *                             }
     *                         )
     *                     ),
     *                     @OA\Property(property="phone", type="string", description="Supplier Contact phone"),
     *                     @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension"),
     *                     @OA\Property(property="phones", type="array", description="Supplier Contact aditional phones",
     *                         @OA\Items(ref="#/components/schemas/PhonesRaw")
     *                     ),
     *                     @OA\Property(property="position", type="string", description="Supplier Contact position"),
     *                     @OA\Property(property="is_main", type="boolean", description="Supplier Contact is main"),
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
            'email' => $this->email,
            'emails' => $this->emails,
            'phone' => $this->phone,
            'phone_extension' => $this->phone_extension,
            'phones' => $this->phones,
            'position' => $this->position,
            'is_main' => $this->is_main,
        ];
    }
}
