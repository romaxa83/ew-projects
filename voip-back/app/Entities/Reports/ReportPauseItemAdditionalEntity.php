<?php

namespace App\Entities\Reports;

class ReportPauseItemAdditionalEntity
{
    public int $pause;
    public int $total_pause_time;

    public function __construct(array $data)
    {
        $this->pause = data_get($data, 'pause', 0);
        $this->total_pause_time = data_get($data, 'total_pause_time', 0);
    }
}

