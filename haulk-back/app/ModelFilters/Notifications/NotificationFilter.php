<?php

namespace App\ModelFilters\Notifications;

use EloquentFilter\ModelFilter;

class NotificationFilter extends ModelFilter
{
    public function type(string $value): void
    {
        $this->where('type', $value);
    }

    public function status(string $value): void
    {
        $this->where('status', $value);
    }

    public function place(string $value): void
    {
        $this->where('place', $value);
    }
}

