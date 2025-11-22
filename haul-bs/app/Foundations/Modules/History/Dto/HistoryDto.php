<?php

namespace App\Foundations\Modules\History\Dto;

use App\Foundations\Modules\History\Enums\HistoryType;
use Carbon\CarbonImmutable;

class HistoryDto
{
    public HistoryType $type;
    public string $msg;
    public array $msgAttr = [];
    public string $modelType;
    public int|string $modelId;
    public int|string|null $userId;
    public string|null $userRole;
    public CarbonImmutable $performedAt;
    public string|null $performedTimezone;
    public array $details = [];


    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->type = HistoryType::fromValue(data_get($data, 'type'));
        $self->msg = data_get($data, 'msg');
        $self->msgAttr = data_get($data, 'msg_attr', []);
        $self->modelType = data_get($data, 'model_type');
        $self->modelId = data_get($data, 'model_id');
        $self->userId = data_get($data, 'user_id');
        $self->userRole = data_get($data, 'user_role');
        $self->performedAt = data_get($data, 'performed_at');
        $self->performedTimezone = data_get($data, 'performed_timezone');
        $self->details = data_get($data, 'details', []);

        return $self;
    }
}
