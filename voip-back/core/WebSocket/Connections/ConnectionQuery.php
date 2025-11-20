<?php

namespace Core\WebSocket\Connections;

class ConnectionQuery
{
    private int $id;
    private string $query;
    private array $variables;
    private ?string $operationName;

    public function __construct(int $id, array $payload)
    {
        $this->id = $id;

        $this->query = $payload['query'];
        $this->variables = $payload['variables'];
        $this->operationName = $payload['operationName'] ?? null;
    }

    public function query(): string
    {
        return $this->query;
    }

    public function variables(): array
    {
        return $this->variables;
    }

    public function operationName(): ?string
    {
        return $this->operationName;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
