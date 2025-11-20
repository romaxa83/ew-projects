<?php

namespace App\Repositories\FcmNotification;

use App\Abstractions\AbstractRepository;
use App\Models\Notification\FcmTemplate;
use Illuminate\Database\Eloquent\Builder;

class FcmNotificationRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return FcmTemplate::query();
    }

    public function getOneByType(string $type, $relation = [])
    {
        return $this->query()
            ->with($relation)
            ->where('type', $type)
            ->first();
    }

    public function getByID($id, $relation = [])
    {
        return $this->query()
            ->with($relation)
            ->where('id', $id)
            ->first();
    }
}
