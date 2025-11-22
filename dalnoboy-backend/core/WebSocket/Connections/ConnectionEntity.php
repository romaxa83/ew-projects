<?php

namespace Core\WebSocket\Connections;

use Core\WebSocket\Contracts\Subscribable;
use Ratchet\ConnectionInterface;

class ConnectionEntity
{
    private int $id;
    private ConnectionInterface $connection;
    private ?Subscribable $user = null;
    private ConnectionQuery $connectionQuery;
    private string $subscriptionName;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function setConnection(ConnectionInterface $connection): void
    {
        $this->connection = $connection;
    }

    public function getUser(): ?Subscribable
    {
        return $this->user;
    }

    public function setUser(?Subscribable $user = null): void
    {
        $this->user = $user;
    }

    public function getConnectionQuery(): ConnectionQuery
    {
        return $this->connectionQuery;
    }

    public function setConnectionQuery(ConnectionQuery $connectionQuery): void
    {
        $this->connectionQuery = $connectionQuery;
    }

    public function hasSubscriptionName(): bool
    {
        return isset($this->subscriptionName);
    }

    public function getSubscriptionName(): string
    {
        return $this->subscriptionName;
    }

    public function setSubscriptionName(string $subscriptionName): void
    {
        $this->subscriptionName = $subscriptionName;
    }
}
