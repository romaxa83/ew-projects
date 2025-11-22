<?php

namespace Core\Sms;

use Core\Sms\Transport\Transport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class SendQueuedSms
{
    use Queueable;

    public ?int $tries;

    public ?int $timeout;

    public bool $shouldBeEncrypted = false;

    public function __construct(
        public SmsMessage $message,
        public Transport $transport,
    ) {
        $smsable = $message->getSmsable();

        $this->tries = property_exists($smsable, 'tries') ? $smsable->tries : null;
        $this->timeout = property_exists($smsable, 'timeout') ? $smsable->timeout : null;
        $this->afterCommit = property_exists($smsable, 'afterCommit') ? $smsable->afterCommit : null;
        $this->shouldBeEncrypted = $smsable instanceof ShouldBeEncrypted;
    }

    public function handle(): void
    {
        $this->transport->send($this->message);
    }

    public function displayName(): string
    {
        return get_class($this->message->getSmsable());
    }

    public function failed($e): void
    {
        if (method_exists($this->message->getSmsable(), 'failed')) {
            $this->message->getSmsable()->failed($e);
        }
    }

    public function backoff(): ?int
    {
        return $this->message->getSmsable()->backoff ?? null;
    }

    public function __clone()
    {
        $this->message = clone $this->message;

        $transport = clone $this->transport;
        $transport->unsetQueue();

        $this->transport = $transport;
    }
}
