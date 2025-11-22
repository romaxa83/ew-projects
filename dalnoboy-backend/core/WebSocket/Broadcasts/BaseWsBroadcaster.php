<?php

namespace Core\WebSocket\Broadcasts;

use Core\WebSocket\Contracts\Subscribable;
use JsonException;
use WebSocket\BadOpcodeException;

abstract class BaseWsBroadcaster
{
    public const NOTIFICATION_TYPE = '_notification';

    protected ?Subscribable $user;

    public function __construct(protected WsBroadcastClient $broadcastClient)
    {
        $broadcastClient->setSubscriptionEndpoint(
            $this->getSubscriptionEndpoint()
        );
    }

    abstract protected function getSubscriptionEndpoint(): string;

    public function setUser(?Subscribable $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @throws BadOpcodeException
     * @throws JsonException
     */
    public function notify(string $subscription, array $context = []): void
    {
        $message = [
            'type' => self::NOTIFICATION_TYPE,
            'name' => $subscription,
            'context' => $context,
            'user' => $this->user,
        ];

        $this->send(json_encode($message, JSON_THROW_ON_ERROR));
    }

    /** @throws BadOpcodeException */
    public function send(string $data): void
    {
        $this->broadcastClient->send($data);
    }
}
