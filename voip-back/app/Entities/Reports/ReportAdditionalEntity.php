<?php

namespace App\Entities\Reports;

class ReportAdditionalEntity
{
    public int $total_calls;
    public int $total_answer_calls;
    public int $total_dropped_calls;
    public int $total_transfer_calls;
    public int $total_wait;
    public int $total_time;
    public int $total_pause;
    public int $total_pause_time;

    public function __construct(array $data)
    {
        $this->total_calls = data_get($data, 'total_calls', 0);
        $this->total_answer_calls = data_get($data, 'total_answer_calls', 0);
        $this->total_dropped_calls = data_get($data, 'total_dropped_calls', 0);
        $this->total_transfer_calls = data_get($data, 'total_transfer_calls', 0);
        $this->total_wait = data_get($data, 'total_wait', 0);
        $this->total_time = data_get($data, 'total_time', 0);
        $this->total_pause = data_get($data, 'total_pause', 0);
        $this->total_pause_time = data_get($data, 'total_pause_time', 0);
    }
}
