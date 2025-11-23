<?php

namespace App\Http\Resources\Employees\SalesTeam;

use App\Models\Employee;
use App\Models\Employee\EfficiencyPlan;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EfficiencyPlan
 */
class EfficiencyPlanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->getDate(),
            'conversion_local_team' => $this->conversion_local_team,
            'conversion_long_team' => $this->conversion_long_team,
        ];
    }
}
