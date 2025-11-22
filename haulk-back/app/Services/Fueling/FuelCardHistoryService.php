<?php

namespace App\Services\Fueling;

use App\Enums\Fueling\FuelCardAssignedTypeEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Fueling\FuelCardHistory;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;

class FuelCardHistoryService
{
    public function assignedDriver(User $user, $fuelCardId): User
    {
        $fuelCard = FuelCard::query()->whereKey($fuelCardId)->first();
        $this->unassignedDriver($user);
        $this->unassignedFuelCard($fuelCard);

        FuelCardHistory::query()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $user->id,
            'active' => true,
            'date_assigned' => now(),
        ]);

        return $user;
    }

    public function unassignedDriver(User $user, int $fuelCardId = null): User
    {
        $histories = FuelCardHistory::query()
            ->where('user_id', '=', $user->id)
            ->when($fuelCardId, fn(Builder $builder) => $builder->where('fuel_card_id', $fuelCardId))
            ->where('active', '=', true)
            ->get();

        if ($histories->count() > 1) {
            $history = $histories->first();
            $this->unassigned($history);
        }
        return $user;
    }

    public function unassignedAllDriver(User $user, int $fuelCardId = null): User
    {
        $histories = FuelCardHistory::query()
            ->where('user_id', '=', $user->id)
            ->when($fuelCardId, fn(Builder $builder) => $builder->where('fuel_card_id', $fuelCardId))
            ->where('active', '=', true)
            ->get();
        foreach ($histories as $history) {
            $this->unassigned($history);
        }
        return $user;
    }

    public function unassignedFuelCard(FuelCard $fuelCard)
    {
        $history = FuelCardHistory::query()
            ->where('fuel_card_id', '=', $fuelCard->id)
            ->where('active', '=', true)
            ->first();
        if ($history) {
            $this->unassigned($history);
        }
    }

    public function assigned(FuelCard $fuelCard, array $args = []): void
    {
        if ($args['type'] === FuelCardAssignedTypeEnum::REPLACE) {
            $history = FuelCardHistory::query()
                ->where('user_id', '=', $args['driver_id'])
                ->where('active', '=', true)
                ->first();
            $this->unassigned($history);
        }

        FuelCardHistory::query()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $args['driver_id'],
            'active' => true,
            'date_assigned' => now(),
        ]);
    }

    private function unassigned(FuelCardHistory $history = null): void
    {
        if ($history)
        {
            $history->active = false;
            $history->date_unassigned = now();
            $history->save();
        }
    }
}
