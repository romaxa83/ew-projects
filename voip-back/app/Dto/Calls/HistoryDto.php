<?php

namespace App\Dto\Calls;

use App\Enums\Calls\HistoryStatus;
use App\Models\Calls\History;

final class HistoryDto
{
    public string $callDate;
    public string $fromNum;
    public string $fromName;
    public string $dialed;
    public ?string $dialedName;
    public int $duration;
    public int $billsec;
    public ?string $serialNumbers;
    public ?string $caseID;
    public string $lastapp;
    public HistoryStatus $status;
    public string $uniqueid;
    public string $channel;
    public ?int $employeeID;
    public ?int $fromEmployeeID;
    public ?int $departmentID;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->callDate = data_get($args, 'calldate');
        $self->fromNum = data_get($args, 'src');
        $self->fromName = data_get($args, 'clid');
        $self->dialed = $args['dst'] ?? '??';
        $self->dialedName = data_get($args, 'dialed_name');
        $self->duration = data_get($args, 'duration');
        $self->billsec = data_get($args, 'billsec');
        $self->serialNumbers = data_get($args, 'serial');
        $self->caseID = data_get($args, 'case_id');
        $self->lastapp = data_get($args, 'lastapp');
        $self->uniqueid = data_get($args, 'uniqueid');
        $self->channel = data_get($args, 'channel');
        $self->employeeID = data_get($args, 'employee_id');
        $self->fromEmployeeID = data_get($args, 'from_employee_id');
        $self->departmentID = data_get($args, 'department_id');

        $self->status = self::getStatus($args);

        return $self;
    }

    private static function getStatus($args): HistoryStatus
    {
        if(data_get($args, 'true_reason_hangup') == 'ANSWER'){
            $args['true_reason_hangup'] = HistoryStatus::ANSWERED;
        }
        if(data_get($args, 'disposition') == 'ANSWER'){
            $args['disposition'] = HistoryStatus::ANSWERED;
        }

        $cdrStatusNoAnswer = 'NO ANSWER';

        $statusFormat = data_get($args, 'disposition') == $cdrStatusNoAnswer
            ? HistoryStatus::NO_ANSWER
            : mb_strtolower(data_get($args, 'disposition'));

//        $statusReasonFormat = data_get($args, 'true_reason_hangup') == $cdrStatusNoAnswer
//            ? HistoryStatus::NO_ANSWER
//            : mb_strtolower(data_get($args, 'true_reason_hangup'));

        $status = HistoryStatus::fromValue($statusFormat);

//        if ($status->isNoAnswer()) {
//            if ($statusReasonFormat) {
//                $status = HistoryStatus::fromValue($statusReasonFormat);
//            }
//        }

        return $status;
    }
}

