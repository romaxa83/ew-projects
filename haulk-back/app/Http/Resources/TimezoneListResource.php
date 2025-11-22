<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimezoneListResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="TimezoneListResource",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              description="Timezones data",
     *              allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="timezone", type="string", description="Timezone"),
     *                          @OA\Property(property="title", type="string", description="Timezone titlee"),
     *                      )
     *           }
     *           ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'timezone' => $this['timezone'],
            'title' => $this['title'],
            'country_code' => $this['country_code'],
            'country_name' => $this['country_name'],
        ];
    }
}
