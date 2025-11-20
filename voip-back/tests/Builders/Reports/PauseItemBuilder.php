<?php

namespace Tests\Builders\Reports;

use App\Models\Reports;
use App\Models\Reports\Report;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class PauseItemBuilder extends BaseBuilder
{
    public function modelClass(): string
    {
        return Reports\PauseItem::class;
    }

    public function setReport(Report $report): self
    {
        $this->data['report_id'] = $report->id;
        return $this;
    }

    public function setPauseAt(CarbonImmutable $value): self
    {
        $this->data['pause_at'] = $value;
        return $this;
    }

    public function setUnpauseAt(CarbonImmutable $value): self
    {
        $this->data['unpause_at'] = $value;
        return $this;
    }
}

