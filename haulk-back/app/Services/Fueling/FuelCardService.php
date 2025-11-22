<?php

namespace App\Services\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Fueling\FuelCard;
use DB;
use Exception;

class FuelCardService
{
    protected FuelCardHistoryService $service;

    public function __construct(FuelCardHistoryService $service)
    {
        $this->service = $service;
    }
    public function create(array $attributes): FuelCard
    {
        try {
            DB::beginTransaction();
            $fuelCard = FuelCard::query()->make($attributes);

            $this->checkAndChangeActive($fuelCard, $attributes);
            $fuelCard->saveOrFail();

            DB::commit();

            return $fuelCard;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    private function checkAndChangeActive(FuelCard $fuelCard, array $attributes): void
    {
        $fuelCard->active = $attributes['status'] === FuelCardStatusEnum::ACTIVE ? true : false;
        $fuelCard->deactivated_at = $attributes['status'] === FuelCardStatusEnum::INACTIVE ? now() : null;
    }

    public function update(FuelCard $fuelCard, array $attributes): FuelCard
    {
        try {
            DB::beginTransaction();
            $this->checkAndChangeActive($fuelCard, $attributes);

            $fuelCard->update($attributes);

            DB::commit();

            return $fuelCard;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * @throws HasRelatedEntitiesException
     */
    public function destroy(FuelCard $fuelCard): FuelCard
    {
        if ($fuelCard->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }
        $fuelCard->status = FuelCardStatusEnum::DELETED;
        $fuelCard->save();
        $fuelCard->delete();

        return $fuelCard;
    }
}
