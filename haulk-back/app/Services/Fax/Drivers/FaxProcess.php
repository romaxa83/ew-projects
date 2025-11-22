<?php

namespace App\Services\Fax\Drivers;

class FaxProcess
{
    public int $failCounter;

    public int $queueCounter;

    private FaxSendResponse $response;

    public function __construct()
    {
        $this->failCounter = 0;

        $this->queueCounter = 0;
    }

    public function fail(): self
    {
        $this->failCounter++;
        $this->queueCounter = 0;

        return $this;
    }

    public function inQueue(): self
    {
        $this->queueCounter++;

        return $this;
    }

    public function getResponse(): FaxSendResponse
    {
        return $this->response;
    }

    public function setResponse(FaxSendResponse $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function hasQueueAttempts(): bool
    {
        return $this->getInQueueCount() <= config('fax.queue.tries_after.status_in_queue');
    }

    public function getInQueueCount(): int
    {
        return $this->queueCounter;
    }

    public function hasFailAttempts(): bool
    {
        return $this->getFailCount() <= config('fax.queue.tries_after.status_fail');
    }

    public function getFailCount(): int
    {
        return $this->failCounter;
    }

    public function getHoldAfterFail(): int
    {
        return config('fax.queue.hold_after.status_fail');
    }

    public function getHoldAfterInQueue(): int
    {
        return config('fax.queue.hold_after.status_in_queue');
    }
}
