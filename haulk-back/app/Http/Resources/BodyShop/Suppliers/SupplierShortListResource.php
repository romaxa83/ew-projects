<?php

namespace App\Http\Resources\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Supplier
 */
class SupplierShortListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *     schema="SupplierRawShort",
     *     type="object",
     *     allOf={
     *         @OA\Schema(
     *             required={"id", "name"},
     *             @OA\Property(property="id", type="integer", description="Supplier id"),
     *             @OA\Property(property="name", type="string", description="Supplier Name"),
     *             @OA\Property(property="гкд", type="string", description="Supplier url"),
     *             @OA\Property(
     *                         property="contact",
     *                         description="Supplier Contact data",
     *                         type="object",
     *                         allOf={
     *                             @OA\Schema(
     *                                 required={"name", "phone", "email"},
     *                                 @OA\Property(property="name", type="string", description="Supplier Contact Name"),
     *                                 @OA\Property(property="email", type="string", description="Supplier Contact email"),
     *                                 @OA\Property(property="phone", type="string", description="Supplier Contact phone"),
     *                                 @OA\Property(property="phone_extension", type="string", description="Supplier Contact phone extension"),
     *                                 @OA\Property(property="position", type="string", description="Supplier Contact position"),
     *                             )
     *                         }
     *                     ),
     *         )
     *     }
     * )
     *
     * @OA\Schema(
     *     schema="SupplierShortList",
     *     @OA\Property(
     *         property="data",
     *         description="Supplier paginated list",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/SupplierRawShort")
     *     ),
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
                    'phone' => $mainContact->phone,
                    'phone_extension' => $mainContact->phone_extension,
                    'position' => $mainContact->position,
                ]
                : null,
        ];
    }
}
