<?php

namespace App\Repositories\Notifications;

use App\Models\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository
{
    public function getAllPagination(array $filters = []): LengthAwarePaginator
    {
        return Notification::query()
            ->filter($filters)
            ->latest()
            ->paginate(
                $filters['per_page'] ?? 10,
                ['*'],
                'page',
                $filters['page'] ?? 1
            );
    }

    public function getByID($id): ?Notification
    {
        return Notification::query()
            ->where('id', $id)
            ->first();
    }

    public function getByIDs(array $id): Collection
    {
        return Notification::query()
            ->whereIn('id', $id)
            ->get();
    }
}
