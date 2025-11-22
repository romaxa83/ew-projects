<?php

namespace App\Repositories\Support;

use App\Models\Support\Message;
use App\Repositories\AbstractRepository;

class MessageRepository extends AbstractRepository
{
    public function query()
    {
        return Message::query();
    }

    public function countByStatus(int $status): int
    {
        return $this->query()->where('status', $status)->count();
    }
}
