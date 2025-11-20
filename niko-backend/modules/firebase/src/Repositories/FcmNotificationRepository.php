<?php

namespace WezomCms\Firebase\Repositories;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\Firebase\Types\FcmNotificationStatus;

class FcmNotificationRepository extends AbstractRepository
{
    protected function query()
    {
        return FcmNotification::query();
    }

    public function getAllByUser(
        $userId,
        array $params = [],
        $status = [FcmNotificationStatus::CREATED],
        $orderBy = 'asc'
    ): Collection
    {

        $this->initParams($params);

        $query = $this->query()
            ->with(['order.group'])
            ->where('user_id', $userId)
            ->whereIn('status', $status)
        ;

        return $query
            ->offset($this->getOffset())
            ->limit($this->getLimit())
            ->orderBy('created_at', $orderBy)
            ->get();
    }

    public function countByUserAndTime($userId, $timestampFromFront, $status = [FcmNotificationStatus::CREATED]): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', DateFormatter::convertTimestampForBack($timestampFromFront))
            ->whereIn('status', $status)
            ->count();
    }

    public function countByUser($userId, $status = [FcmNotificationStatus::CREATED]): int
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereIn('status', $status)
            ->count();
    }
}
