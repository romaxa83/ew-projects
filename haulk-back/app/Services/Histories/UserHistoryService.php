<?php


namespace App\Services\Histories;


use App\Models\History\UserHistory;
use App\Models\Users\User;
use App\Repositories\Roles\RoleRepository;
use Exception;
use Illuminate\Support\Collection;

class UserHistoryService
{
    private function getRoleRepository(): RoleRepository
    {
        return resolve(RoleRepository::class);
    }

    public function track(User $performerUser, User $targetUser, string $operation): void
    {
        try {
            $record = new UserHistory();
            $record->performer_id = $performerUser->id;
            $record->user_id = $targetUser->id;
            $record->role_id = $targetUser->roles->first()->id;
            $record->full_name = $targetUser->full_name;
            $record->email = $targetUser->email;
            $record->operation = $operation;
            $record->save();
        } catch (Exception $e) {}
    }

    public function getHistory(int $companyId, string $start, string $end, int $roleId): Collection
    {
        return UserHistory::withoutGlobalScopes(
        )->selectRaw(
            'date(' . UserHistory::TABLE_NAME . '.created_at) as date,
            ' . UserHistory::TABLE_NAME . '.user_id,
            ' . UserHistory::TABLE_NAME . '.full_name,
            ' . UserHistory::TABLE_NAME . '.email,
            ' . UserHistory::TABLE_NAME . '.operation'
        )->where(
            [
                ['carrier_id', $companyId],
                ['role_id', $roleId],
                ['created_at', '>=', $start],
                ['created_at', '<=', $end],
            ]
        )->orderBy(
            'created_at'
        )->get();
    }

    public function getDriverHistory(int $companyId, string $start, string $end): Collection
    {
        $driverRole = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);

        if ($driverRole) {
            return $this->getHistory($companyId, $start, $end, $driverRole->id);
        }

        return collect();
    }
}
