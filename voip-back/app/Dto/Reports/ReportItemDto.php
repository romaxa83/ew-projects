<?php

namespace App\Dto\Reports;

use App\Enums\Reports\ReportStatus;

final class ReportItemDto
{
    public string|int $reportID;
    public string $callID;
    public string $status;
    public string $num;
    public ?string $name;
    public int $wait;
    public int $totalTime;
    public string $callAt;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->reportID = data_get($args, 'report_id');
        $self->callID = data_get($args, 'callid');
        $self->num = data_get($args, 'num', ' ');
        $self->status = ReportStatus::fromValue(data_get($args, 'status'));
        $self->name = data_get($args, 'name');
        $self->wait = data_get($args, 'wait');
        $self->totalTime = data_get($args, 'total_time', 0);
        $self->callAt = data_get($args, 'call_at');

        return $self;
    }
}

