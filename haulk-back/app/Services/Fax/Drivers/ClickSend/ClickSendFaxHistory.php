<?php

namespace App\Services\Fax\Drivers\ClickSend;

class ClickSendFaxHistory
{
    public const STATUS_FAILED = 'Failed';
    public const STATUS_SENT = 'Sent';
    public const STATUS_IN_QUEUE = 'In queue';
    public const STATUS_QUEUED = 'Queued';
    public const STATUS_QUEUED_WAIT_SEND = 'Queued:WaitSend';
    public const STATUS_WAIT_APPROVAL = 'WaitApproval';
    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_INVALID_RECIPIENT = 'INVALID_RECIPIENT';

    public const STATUS_MESSAGE_SENT = 'Sent';
    public const STATUS_MESSAGE_FAX_SUCCESSFULLY_SENT = 'Fax successfully sent';

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getMessageId(): string
    {
        return $this->data['message_id'];
    }

    public function getFrom(): string
    {
        return $this->data['from'];
    }

    public function getTo(): string
    {
        return $this->data['to'];
    }

    public function isFailed(): bool
    {
        return $this->getStatus() === self::STATUS_FAILED;
    }

    public function getStatus(): string
    {
        return $this->data['status'];
    }

    public function isSent(): bool
    {
        return $this->getStatus() === self::STATUS_SENT
            && $this->getStatusMessage() === self::STATUS_MESSAGE_FAX_SUCCESSFULLY_SENT;
    }

    public function getStatusMessage(): string
    {
        return $this->data['status_text'];
    }

    public function isInvalidRecipient(): bool
    {
        return $this->getStatus() === self::STATUS_INVALID_RECIPIENT;
    }

    public function isInQueue(): bool
    {
        return $this->getStatus() === self::STATUS_IN_QUEUE
            || $this->getStatus() === self::STATUS_WAIT_APPROVAL
            || $this->getStatus() === self::STATUS_QUEUED_WAIT_SEND
            || $this->getStatus() === self::STATUS_QUEUED
            || $this->getStatus() === self::STATUS_SCHEDULED
            || (
                $this->getStatus() === self::STATUS_SENT
                && $this->getStatusMessage() === self::STATUS_MESSAGE_SENT
            )//
            ;
    }
}
