<?php

namespace Core\Chat\Filters\Messages;

use Core\Chat\Models\Message;
use EloquentFilter\ModelFilter;

/**
 * @mixin Message
 */
class MessageFilter extends ModelFilter
{
    public function id(int $id): void
    {
        $this->where(
            $this->getModel()
                ->getTable() . '.id',
            $id
        );
    }

    public function ids(array $ids): void
    {
        $this->whereIn(
            $this->getModel()
                ->getTable() . '.id',
            $ids
        );
    }

    public function messagesBefore(int $id): void
    {
        $this->where(
            $this->getModel()
                ->getTable() . '.id',
            '<',
            $id
        );
    }
}
