<?php

namespace App\Services\Fax\Drivers\Log;

use App\Services\Fax\Drivers\FaxSendResponse;

class LogFaxSendResponse implements FaxSendResponse
{

    public function isSuccess(): bool
    {
        return true;
    }

    public function isInQueue(): bool
    {
        return false;
    }

    public function isFail(): bool
    {
        return false;
    }

    public function isSent(): bool
    {
        return true;
    }

    public function refreshStatuses(): void
    {
    }

    public function isInvalidRecipient(): bool
    {
        return false;
    }
}
