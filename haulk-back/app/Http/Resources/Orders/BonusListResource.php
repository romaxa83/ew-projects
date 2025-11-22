<?php


namespace App\Http\Resources\Orders;


use App\Models\Orders\Bonus;
use Illuminate\Http\Resources\Json\JsonResource;

class BonusListResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @OA\Schema(
     *    schema="BonusListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Orders bonuses list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/BonusResourceRaw")
     *    ),
     * )
     *
     */
    public function toArray($request): array
    {
        /**@var Bonus $this*/
        return [
            'id' => $this->id,
            'type' => $this->type,
            'price' => (double) $this->price,
            'to' => $this->to
        ];
    }
}
