<?php

namespace App\Entities\Reports;

class ReportItemAdditionalEntity
{
    public int $total_calls;
    public int $total_dropped;
    public int $total_wait;
    public int $total_time;

    public function __construct(array $data)
    {
        $this->total_calls = data_get($data, 'total_calls', 0);
        $this->total_dropped = data_get($data, 'total_dropped', 0);
        $this->total_wait = data_get($data, 'total_wait', 0);
        $this->total_time = data_get($data, 'total_time', 0);
    }
}
