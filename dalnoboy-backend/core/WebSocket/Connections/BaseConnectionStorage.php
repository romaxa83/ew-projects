<?php

namespace Core\WebSocket\Connections;

use Core\Exceptions\Subscriptions\SubscriptionAuthException;
use Core\Traits\Auth\AuthGuardsTrait;
use Core\WebSocket\Services\WsAuthService;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Ratchet\ConnectionInterface;

abstract class BaseConnectionStorage
{
    use AuthGuardsTrait;

    protected WsAuthService $authService;

    /** @var array<ConnectionEntity> */
    private array $connections = [];

    /** @var array<ConnectionEntity> */
    private array $tempConnections = [];

    public function __construct()
    {
        $this->setAuthService();
    }

    abstract protected function setAuthService(): void;

    /** @throws SubscriptionAuthException */
    public function addConnection(ConnectionInterface $conn, array $payload): void
    {
        $bearer = Arr::get($payload, 'Authorization');

        if (!$bearer) {
            throw new SubscriptionAuthException('Authorization token not provided');
        }

        if (!$user = $this->authService->getUserByBearer($bearer)) {
            throw new SubscriptionAuthException('Authorization token is invalid');
        }

        $connectionId = $this->getConnectionId($conn);

        $entity = new ConnectionEntity();
        $entity->setId($this->getConnectionId($conn));
        $entity->setConnection($conn);
        $entity->setUser($user);

        $this->tempConnections[$connectionId] = $entity;
    }

    protected function getConnectionId(ConnectionInterface $conn): int
    {
        return spl_object_id($conn);
    }

    public function updateConnection(ConnectionInterface $conn, array $payload, int $id): void
    {
        $entity = $this->tempConnections[$this->getConnectionId($conn)] ?? null;

        if (!$entity) {
            return;
        }

        unset($this->tempConnections[$this->getConnectionId($conn)]);

        $entity->setSubscriptionName($this->getSubscriptionName($payload['query']));
        $entity->setConnectionQuery(new ConnectionQuery($id, $payload));

        $this->connections[$entity->getSubscriptionName()][$entity->getId()] = $entity;
    }

    private function getSubscriptionName(string $query): string
    {
        $query = preg_replace('/\\n/', '', $query);
        preg_match('/subscription?\s*\w+?\s*{\s+(\w+)/', $query, $matches);

        if (!isset($matches[1])) {
            throw new InvalidArgumentException('Not a valid subscription query');
        }

        return $matches[1];
    }

    /**
     * @return array<ConnectionEntity>
     */
    public function getConnectionsByMessagePayload(array $messagePayload): array
    {
        $subscriptionName = $messagePayload['name'];
        $userId = Arr::get($messagePayload, 'user.id');

        $connections = [];

        foreach ($this->connections[$subscriptionName] ?? [] as $connection) {
            if (
                $connection->hasSubscriptionName() &&
                $connection->getSubscriptionName() === $subscriptionName &&
                (!$userId || $connection->getUser()?->getKey() === $userId)
            ) {
                $connections[] = $connection;
            }
        }

        return $connections;
    }

    public function removeConnection(ConnectionInterface $conn): void
    {
        unset($this->connections[$this->getConnectionId($conn)]);
    }
}
