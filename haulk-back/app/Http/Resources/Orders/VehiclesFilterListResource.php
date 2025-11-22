<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiclesFilterListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="VehiclesFilterListResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *            allOf={
     *                @OA\Schema(
     *                    description="Vehicle type data",
     *                    required={"label", "value"},
     *                    @OA\Property(property="label", type="string", description="Item label"),
     *                    @OA\Property(property="value", type="string", description="Item value"),
     *                    @OA\Property(property="children", type="array", description="Item children",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    description="Vehicle type data",
     *                                    required={"label", "value"},
     *                                    @OA\Property(property="label", type="string", description="Item label"),
     *                                    @OA\Property(property="value", type="string", description="Item value"),
     *                                    @OA\Property(property="children", type="array", description="Item children",
     *                                        @OA\Items(
     *                                            allOf={
     *                                                @OA\Schema(
     *                                                    description="Vehicle type data",
     *                                                    required={"label", "value"},
     *                                                    @OA\Property(property="label", type="string", description="Item label"),
     *                                                    @OA\Property(property="value", type="string", description="Item value"),
     *                                                )
     *                                            }
     *                                        )
     *                                    ),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                )
     *            }
     *        )
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'label' => $this['label'],
            'value' => $this['value'],
            'children' => $this['children'],
        ];
    }
}
