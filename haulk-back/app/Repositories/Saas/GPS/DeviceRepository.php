<?php

namespace App\Repositories\Saas\GPS;

use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\GPS\Device;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class DeviceRepository
{
    public function getBy(
        $field,
        $value,
        array $relations = [],
        $withException = false,
        $exceptionMessage = 'Model not found',
        $withoutId = null
    ): ?Model
    {
        $result = Device::query()
            ->withTrashed()
            ->with($relations)
            ->when($withoutId, fn(Builder $b): Builder => $b->whereNot('id', $withoutId))
            ->where($field, $value)
            ->first()
        ;

        if ($withException && null === $result) {
            throw new DomainException($exceptionMessage, Response::HTTP_NOT_FOUND);
        }

        return $result;
    }

    public function hasActiveAtVehicle(int $companyId): bool
    {
        return Device::query()
            ->where('company_id', $companyId)
            ->where('status', DeviceStatus::ACTIVE)
            ->where(function($q){
                $q->has('truck')
                    ->orHas('trailer');
            })
            ->exists();
    }

    public function hasActive(int $companyId): bool
    {
        return Device::query()
            ->where('company_id', $companyId)
            ->where('status', DeviceStatus::ACTIVE)
            ->exists();
    }

    public function getAllImei(): array
    {
        return Device::query()
            ->toBase()
            ->get()
            ->pluck('imei', 'imei')
            ->toArray();
    }
}

