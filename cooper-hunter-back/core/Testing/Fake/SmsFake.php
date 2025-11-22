<?php

namespace Core\Testing\Fake;

use Closure;
use Core\Contracts\Sms\Smsable;
use Core\Contracts\Sms\SmsQueue;
use Core\Contracts\Sms\SmsTransportContract;
use Core\Sms\SmsFactory;
use Illuminate\Contracts\Queue\Factory as QueueContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ReflectsClosures;
use PHPUnit\Framework\Assert as PHPUnit;

class SmsFake implements SmsFactory, SmsQueue, SmsTransportContract
{
    use ReflectsClosures;

    protected ?string $transport;
    protected QueueContract $queue;

    protected array $sentSms = [];

    protected array $queuedSms = [];

    public function to($phone): self
    {
        return $this;
    }

    public function send(Smsable $smsable): void
    {
        if ($smsable instanceof ShouldQueue) {
            $this->queuedSms[] = $smsable;
        } else {
            $this->sentSms[] = $smsable;
        }
    }

    public function smsTransport(?string $name = null): SmsTransportContract
    {
        $this->transport = $name;

        return $this;
    }

    public function setQueue(QueueContract $queue): SmsTransportContract
    {
        $this->queue = $queue;

        return $this;
    }

    public function assertSent(string $smsable, ?Closure $callback = null): void
    {
        $message = "The expected [$smsable] sms was not sent.";

        if (count($this->queuedSms) > 0) {
            $message .= ' Did you mean to use assertQueued() instead?';
        }

        PHPUnit::assertTrue(
            $this->sent($smsable, $callback)->count() > 0,
            $message
        );
    }

    public function sent(string $smsable, ?Closure $callback = null): Collection
    {
        if (!$this->hasSent($smsable)) {
            return collect();
        }

        $callback = $callback ?: static fn() => true;

        return $this->smsablesOf($smsable)->filter(function ($smsable) use ($callback) {
            return $callback($smsable);
        });
    }

    public function hasSent(string $smsable): bool
    {
        return $this->smsablesOf($smsable)->count() > 0;
    }

    protected function smsablesOf(string $type): Collection
    {
        return collect($this->sentSms)->filter(function ($sms) use ($type) {
            return $sms instanceof $type;
        });
    }

    public function assertQueued(string $smsable, ?Closure $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->queued($smsable, $callback)->count() > 0,
            "The expected [$smsable] sms was not queued."
        );
    }

    public function queued(string $smsable, ?Closure $callback = null): Collection
    {
        if (!$this->hasQueued($smsable)) {
            return collect();
        }

        $callback = $callback ?: static fn() => true;

        return $this->queuedSmsablesOf($smsable)->filter(function ($smsable) use ($callback) {
            return $callback($smsable);
        });
    }

    public function hasQueued(string $smsable): bool
    {
        return $this->queuedSmsablesOf($smsable)->count() > 0;
    }

    protected function queuedSmsablesOf(string $type): Collection
    {
        return collect($this->queuedSms)->filter(function ($sms) use ($type) {
            return $sms instanceof $type;
        });
    }
}
