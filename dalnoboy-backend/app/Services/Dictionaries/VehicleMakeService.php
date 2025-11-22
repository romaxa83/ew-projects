<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\VehicleMakeDto;
use App\Enums\Permissions\GuardsEnum;
use App\Exceptions\Dictionaries\NotUniqVehicleMakeException;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\VehicleMake;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class VehicleMakeService
{
    public function create(VehicleMakeDto $dto, HasGuard $user): VehicleMake
    {
        return $this->editVehicleForm($dto, new VehicleMake(), $user);
    }

    public function update(VehicleMakeDto $dto, VehicleMake $vehicleMake): VehicleMake
    {
        return $this->editVehicleForm($dto, $vehicleMake);
    }

    private function editVehicleForm(VehicleMakeDto $dto, VehicleMake $vehicleMake, ?HasGuard $user = null): VehicleMake
    {
        $this->checkUniq($dto, $vehicleMake);

        $vehicleMake->active = $user?->getGuard() === GuardsEnum::USER ? true : $dto->isActive();
        $vehicleMake->title = $dto->getTitle();
        $vehicleMake->is_moderated = $user?->getGuard() === GuardsEnum::USER ? false : $dto->isModerated();
        $vehicleMake->vehicle_form = $dto->getVehicleForm();
        $vehicleMake->save();

        return $vehicleMake->refresh();
    }

    private function checkUniq(VehicleMakeDto $dto, VehicleMake $vehicleMake): void
    {
        $similar = VehicleMake::whereRaw('lower(title) = ?', [Str::lower($dto->getTitle())])
            ->where('id', '<>', $vehicleMake->id)
            ->first();

        if (!$similar) {
            return;
        }

        throw new NotUniqVehicleMakeException($similar);
    }

    public function delete(VehicleMake $vehicleMake): bool
    {
        $vehicleMake->load(['vehicleModels', 'vehicles']);

        if ($vehicleMake->vehicleModels->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        if ($vehicleMake->vehicles->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $vehicleMake->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return VehicleMake::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return VehicleMake::whereKey($ids)->get();
    }

    /**
     * @param iterable<VehicleMake> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
