<?php

namespace WezomCms\Firebase\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Firebase\Models\FcmNotification;

class FcmNotificationRepository extends AbstractRepository
{
    public function getAllToFront(
        $userId,
        array $filter = [],
        array $order = []
    ): Collection {
        $query = $this->query()
            ->filter($filter)
            ->where('user_id', $userId);
            //->whereIn('status', [FcmNotification::STATUS_SEND, FcmNotification::STATUS_CREATED]);

        if (!empty($order)) {
            foreach ($order as $field => $type) {
                $query->orderBy($field, $type);
            }
        }

        return $query->get();
    }

    protected function query(): Builder
    {
        return FcmNotification::query();
    }
}
