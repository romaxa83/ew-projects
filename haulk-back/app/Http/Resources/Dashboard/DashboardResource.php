<?php

namespace App\Http\Resources\Dashboard;

use App\Models\Users\User;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="DashboardResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        description="",
     *        @OA\Items(
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="title", type="string", description=""),
     *                    @OA\Property(property="key", type="string", description=""),
     *                    @OA\Property(property="value", type="integer", description=""),
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
            'title' => $this['title'],
            'key' => $this['key'],
            'value' => $this['value'],
        ];
    }
}
