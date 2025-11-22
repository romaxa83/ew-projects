<?php

namespace App\Http\Resources\Delivery;

use App\Dto\Delivery\DeliveryRateDto;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="DeliveryRate", type="object",
 *      @OA\Property(property="id", type="string", description="02"),
 *      @OA\Property(property="name", type="string", description="Fedex"),
 *      @OA\Property(property="amount", type="float", description="10"),
 *      @OA\Property(property="date", type="string", description="date"),
 *      @OA\Property(property="text_additional", type="string", description="text_additional"),
 *  )
 * @OA\Schema(schema="DeliveryRateListResource",
 *      @OA\Property(property="data", description="Comments list", type="array",
 *          @OA\Items(ref="#/components/schemas/DeliveryRate")
 *      ),
 *  )
 * @mixin DeliveryRateDto
 */
class DeliveryRateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'date' => $this->getDate()->format('m/d/Y'),
            'amount' => $this->getAmount(),
            'text_additional' => $this->getTextAdditional(),
        ];
    }
}
