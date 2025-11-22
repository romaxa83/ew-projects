<?php

namespace App\DTO\Order;

use App\Traits\AssetData;

class OrderEditDTO
{
    use AssetData;

    private null|int|string $adminId = null;
    private null|int $status = null;

    private bool $changeAdminId;
    private bool $changeStatus;

    private function __construct(array $data)
    {
        $this->changeAdminId = static::checkFieldExist($data, 'adminId');
        $this->changeStatus = static::checkFieldExist($data, 'status');
    }

    public static function byArgs(array $args): self
    {
        $self = new self($args);

        $self->adminId = $args['adminId'] ?? null;
        $self->status = $args['status'] ?? null;

        return $self;
    }

    public function getAdminId(): null|string|int
    {
        return $this->adminId;
    }

    public function getStatus(): null|string
    {
        return $this->status;
    }

    public function changeAdminId(): bool
    {
        return $this->changeAdminId;
    }

    public function changeStatus(): bool
    {
        return $this->changeStatus;
    }
}




