<?php

namespace App\Http\Resources\Locations;

use App\Foundations\Modules\Location\Models\City;
use App\Foundations\Modules\Location\Services\TimezoneService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="CityAutocompleteResource", type="object",
 *     @OA\Property(property="data", type="object", description="City data", allOf={
 *         @OA\Schema(
 *             required={"id", "name", "state_id", "zip", "country_code", "country_name"},
 *             @OA\Property(property="id", type="integer", example="6553"),
 *             @OA\Property(property="name", type="string", example="Aaronsburg"),
 *             @OA\Property(property="zip", type="string", example="16820"),
 *             @OA\Property(property="state_id", type="integer", example="10"),
 *             @OA\Property(property="state_name", type="sring", example="Vermont"),
 *             @OA\Property(property="state_short_name", type="sring", example="VE"),
 *             @OA\Property(property="timezone", type="string", example="America/New_York"),
 *             @OA\Property(property="country_code", type="string", example="us"),
 *             @OA\Property(property="country_name", type="string", example="USA"),
 *         )}
 *     ),
 * )
 *
 * @mixin City
 */

class CityAutocompleteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'zip' => $this->zip,
            'state_id' => $this->state ? $this->state->id : null,
            'state_name' => $this->state ? $this->state->name : null,
            'state_short_name' => $this->state ? $this->state->state_short_name : null,
            'timezone' => resolve(TimezoneService::class)->isValidTimezone($this->timezone) ? $this->timezone : null,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
        ];
    }
}
