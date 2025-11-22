<?php

namespace App\Http\Resources\Orders\Parts\Additional;

use App\Entities\Order\Parts\EcommerceClientEntity;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="EcommerceClientRaw", type="object", allOf={
 *      @OA\Schema(
 *          required={"first_name", "last_name", "email"},
 *          @OA\Property(property="first_name", type="string", example="John"),
 *          @OA\Property(property="last_name", type="string", example="Doe"),
 *          @OA\Property(property="email", type="string", example="john@doe.com"),
 *      )
 * })
 *
 * @mixin EcommerceClientEntity
 */
class EcommerceClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email->getValue(),
        ];
    }
}
