<?php

namespace App\Services\Fax\Drivers;

interface FaxSendResponse
{
    public function isSuccess(): bool;

    public function isSent(): bool;

    public function isInQueue(): bool;

    public function isFail(): bool;

    public function isInvalidRecipient(): bool;

    public function refreshStatuses(): void;
}
