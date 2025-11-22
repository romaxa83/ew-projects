<?php

namespace Core\WebSocket\Jobs;

use Core\WebSocket\Broadcasts\BaseWsBroadcaster;
use Core\WebSocket\Contracts\Subscribable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JsonException;
use WebSocket\BadOpcodeException;
use WebSocket\ConnectionException;

class WsBroadcastJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const QUEUE = 'default';

    protected string $subscription;
    protected ?Subscribable $user = null;
    protected array $context = [];

    public function __construct(
        protected BaseWsBroadcaster $broadcaster
    ) {
        $this->onQueue(static::QUEUE);
    }

    public function toSubscription(string $subscriptionName): self
    {
        $this->subscription = $subscriptionName;

        return $this;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function setUser(?Subscribable $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?Subscribable
    {
        return $this->user;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @throws BadOpcodeException
     * @throws JsonException
     */
    public function handle(): void
    {
        $this->broadcaster->setUser($this->user);
        try {
            $this->broadcaster->notify($this->subscription, $this->context);
        } catch (ConnectionException $e) {
            Log::error($e);
        }
    }
}
