<?php

namespace App\Entities\OneC\Tickets;

use App\Entities\OneC\BaseOnecEntity;
use App\Enums\Tickets\TicketStatusEnum;
use Illuminate\Support\Str;
use JsonException;

class TicketEntity extends BaseOnecEntity
{
    public string $code;
    public string $guid;
    public ?TicketStatusEnum $status = null;

    /**
     * @throws JsonException
     */
    public static function createFromResponse(string $body): self
    {
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        $self = new self();

        $self->setDefaults($data);

        $self->code = $data['doc_number'];
        $self->guid = $data['guid'];

        $status = Str::snake($data['status']);

        if (TicketStatusEnum::hasValue($status)) {
            $self->status = TicketStatusEnum::fromValue($status);
        }

        return $self;
    }
}