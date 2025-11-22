<?php

namespace App\Dto\Alerts\MetaData;

use App\Contracts\Alerts\MetaDataDto;
use App\Models\Support\SupportRequestMessage;

class SupportRequestMessageDto implements MetaDataDto
{

    private SupportRequestMessage $message;

    public static function fromEvent(array $event): SupportRequestMessageDto
    {
        $dto = new self();

        $dto->message = $event['message'];

        return $dto;
    }

    public function getMessage(): SupportRequestMessage
    {
        return $this->message;
    }

}
