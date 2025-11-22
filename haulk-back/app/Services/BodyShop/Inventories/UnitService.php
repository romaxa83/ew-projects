<?php

namespace App\Services\BodyShop\Inventories;

use App\Exceptions\HasRelatedEntitiesException;
use App\Models\BodyShop\Inventories\Unit;
use DB;
use Exception;
use Log;

class UnitService
{
    public function create(array $attributes): Unit
    {
        try {
            DB::beginTransaction();

            /** @var Unit $unit */
            $unit = Unit::query()->make($attributes);
            $unit->saveOrFail();

            DB::commit();

            return $unit;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Unit $unit, array $attributes): Unit
    {
        try {
            DB::beginTransaction();

            $unit->update($attributes);

            DB::commit();

            return $unit;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(Unit $unit): Unit
    {
        if ($unit->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        $unit->delete();

        return $unit;
    }
}
