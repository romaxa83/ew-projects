<?php


namespace App\Http\Resources\Orders;


use App\Models\Orders\Bonus;
use Illuminate\Http\Request;

class BonusResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="BonusResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer",),
     *                @OA\Property(property="type", type="string",),
     *                @OA\Property(property="price", type="number",),
     *                @OA\Property(property="to", type="string", description="User type to", enum={"broker","customer"}, nullable=true),
     *            )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="BonusResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Order bonus data",
     *            allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer",),
     *                @OA\Property(property="type", type="string",),
     *                @OA\Property(property="price", type="number",),
     *                @OA\Property(property="to", type="string", description="User type to", enum={"broker","customer"}, nullable=true),
     *            )
     *        }
     *        ),
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
            'to' => $this->to,
        ];
    }
}
