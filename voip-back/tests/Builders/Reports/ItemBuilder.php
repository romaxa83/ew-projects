<?php

namespace Tests\Builders\Reports;

use App\Models\Reports;
use App\Models\Reports\Report;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class ItemBuilder extends BaseBuilder
{
     public function modelClass(): string
    {
        return Reports\Item::class;
    }

    public function setReport(Report $report): self
    {
        $this->data['report_id'] = $report->id;
        return $this;
    }

    public function setCallAt(CarbonImmutable $value): self
    {
        $this->data['call_at'] = $value;
        return $this;
    }
}
