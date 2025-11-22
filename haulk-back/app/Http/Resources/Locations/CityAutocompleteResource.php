<?php

namespace App\Http\Resources\Locations;

use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityAutocompleteResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *   schema="CityAutocomplete",
     *   type="object",
     *           @OA\Property(
     *              property="data",
     *              type="object",
     *              description="City data",
     *              allOf={
     *                      @OA\Schema(
     *                          required={"id", "status", "name","state_id","zip"},
     *                          @OA\Property(property="id", type="integer", description="City id"),
     *                          @OA\Property(property="name", type="string", description="City name"),
     *                          @OA\Property(property="state_id", type="string", description="State id"),
     *                          @OA\Property(property="timezone", type="string", description="Timezone"),
     *                          @OA\Property(property="country_code", type="string", description="country_code"),
     *                          @OA\Property(property="country_name", type="string", description="country_name"),
     *                      )
     *           }
     *           ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'zip' => $this->zip,
            'state_id' => $this->state ? $this->state->id : null,
            'timezone' => resolve(TimezoneService::class)->isValidTimezone($this->timezone) ? $this->timezone : null,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
        ];
    }
}
