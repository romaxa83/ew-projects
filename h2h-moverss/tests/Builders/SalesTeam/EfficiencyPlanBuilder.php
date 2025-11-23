<?php

namespace Tests\Builders\SalesTeam;

use App\Models\Employee\EfficiencyPlan;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class EfficiencyPlanBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return EfficiencyPlan::class;
    }

    public function date(string|CarbonImmutable $value): self
    {
        if($value instanceof CarbonImmutable) {
            $value = $value->format('Y-m');
        }
        $this->data['date_at'] = EfficiencyPlan::validDate($value);
        return $this;
    }

    public function conversion_local_team(int $value): self
    {
        $this->data['conversion_local_team'] = $value;
        return $this;
    }

    public function conversion_long_team(int $value): self
    {
        $this->data['conversion_long_team'] = $value;
        return $this;
    }
}

