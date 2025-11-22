<?php

namespace App\Services\Fax\Drivers\Fake;

use App\Services\Fax\Drivers\FaxSendResponse;

class FakeFaxSendResponse implements FaxSendResponse
{
    private bool $success;

    private bool $inQueue;

    private bool $isSent;

    private bool $isFailed;

    public function __construct(
        bool $success = true,
        bool $inQueue = false,
        bool $isSent = true,
        bool $isFailed = false
    ) {
        $this->success = $success;
        $this->inQueue = $inQueue;
        $this->isSent = $isSent;
        $this->isFailed = $isFailed;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isInQueue(): bool
    {
        return $this->inQueue;
    }

    public function isFail(): bool
    {
        return $this->isFailed;
    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

    public function refreshStatuses(): void
    {
    }

    public function isInvalidRecipient(): bool
    {
        return false;
    }
}
