<?php

namespace App\Http\Resources\Calculations;

use App\Models\Calculation\LocalHourlyRates;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LocalHourlyRates
 */
class LocalHourlyRatesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'crew_qty' => $this->crew_qty,
            'workday' => $this->workday,
            'holiday' => $this->holiday,
            'peakday' => $this->peakday,
            'season' => $this->season,
        ];
    }
}
