<?php

namespace Tests\Builders\Commercial;

use App\Models\Commercial\CommercialProject;
use App\Models\Technicians\Technician;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class ProjectBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return CommercialProject::class;
    }

    public function setTechnician(Technician $model): self
    {
        $this->data['member_type'] = $model::MORPH_NAME;
        $this->data['member_id'] = $model->id;

        return $this;
    }

    public function setEstimateEndDate(CarbonImmutable $date): self
    {
        $this->data['estimate_end_date'] = $date;

        return $this;
    }

    public function setStatus($value): self
    {
        $this->data['status'] = $value;

        return $this;
    }

    public function setCreatedAt($value): self
    {
        $this->data['created_at'] = $value;

        return $this;
    }

    public function setStartPreCommissioningDate($value): self
    {
        $this->data['start_pre_commissioning_date'] = $value;

        return $this;
    }

    public function setStartCommissioningDate($value): self
    {
        $this->data['start_commissioning_date'] = $value;

        return $this;
    }

    public function setEndCommissioningDate($value): self
    {
        $this->data['end_commissioning_date'] = $value;

        return $this;
    }
}




