<?php

namespace App\Traits\Services\Vehicles;

use App\Http\Requests\Vehicles\VehicleHistoryRequest;
use App\Models\History\History;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Database\Eloquent\Builder;

trait VehicleHistoryTrait
{
    public function getHistoryShort(Vehicle $vehicle, bool $isBodyShop = false)
    {
        $roles = $this->getAllowedUserRoles($isBodyShop);

        $history = History::query()
            ->where(
                [
                    ['model_id', $vehicle->id],
                    ['model_type', get_class($vehicle)],
                ]
            )
            ->whereIn('user_role', $roles)
            ->latest('performed_at')
            ->get();

        return $this->applyHistoryTranslates($history);
    }

    public function getHistoryDetailed(Vehicle $vehicle, VehicleHistoryRequest $request, bool $isBodyShop = false)
    {
        $roles = $this->getAllowedUserRoles($isBodyShop);

        $history = History::query()
            ->where(
                [
                    ['model_id', $vehicle->id],
                    ['model_type', get_class($vehicle)],
                ]
            )
            ->whereIn('user_role', $roles)
            ->whereType(History::TYPE_CHANGES)
            ->filter($request->validated())
            ->latest('id')
            ->paginate($request->per_page);

        return $this->applyHistoryTranslates($history);
    }

    private function applyHistoryTranslates($history)
    {
        foreach ($history as &$h) {
            if (isset($h['meta']) && is_array($h['meta'])) {
                $h['message'] = trans($h['message'], $h['meta']);
            }
        }

        return $history;
    }

    public function getHistoryUsers(Vehicle $vehicle, bool $isBodyShop = false)
    {
        $roles = $this->getAllowedUserRoles($isBodyShop);

        return User::active()
            ->whereHas(
                'roles',
                function (Builder $builder) use ($roles) {
                    $builder->whereIn('name', $roles);
                }
            )
            ->whereIn(
                'id',
                History::query()
                    ->select('user_id')
                    ->where(
                        [
                            ['model_id', $vehicle->id],
                            ['model_type', get_class($vehicle)],
                        ]
                    )
                    ->whereType(History::TYPE_CHANGES)
                    ->getQuery()
            )
            ->orderByRaw('concat(first_name, \' \', last_name) ASC')
            ->get();
    }

    private function getAllowedUserRoles(bool $isBodyShop = false): array
    {
        return $isBodyShop ? User::BS_ROLES : User::COMPANY_ROLES;
    }
}
