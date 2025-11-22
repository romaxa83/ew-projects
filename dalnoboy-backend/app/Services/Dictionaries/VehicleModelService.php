<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\VehicleModelDto;
use App\Enums\Permissions\GuardsEnum;
use App\Exceptions\Dictionaries\NotUniqVehicleModelException;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\VehicleModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class VehicleModelService
{
    public function create(VehicleModelDto $dto, HasGuard $user): VehicleModel
    {
        return $this->editVehicleModel($dto, new VehicleModel(), $user);
    }

    public function update(VehicleModelDto $dto, VehicleModel $vehicleModel): VehicleModel
    {
        return $this->editVehicleModel($dto, $vehicleModel);
    }

    private function editVehicleModel(VehicleModelDto $dto, VehicleModel $vehicleModel, ?HasGuard $user = null): VehicleModel
    {
        $this->checkUniq($dto, $vehicleModel);

        $vehicleModel->active = $user?->getGuard() === GuardsEnum::USER ? true : $dto->isActive();
        $vehicleModel->title = $dto->getTitle();
        $vehicleModel->vehicle_make_id = $dto->getVehicleMakeId();
        $vehicleModel->is_moderated = $user?->getGuard() === GuardsEnum::USER ? false : $dto->isModerated();
        $vehicleModel->save();

        return $vehicleModel->refresh();
    }

    private function checkUniq(VehicleModelDto $dto, VehicleModel $vehicleModel): void
    {
        $similar = VehicleModel::whereRaw('lower(title) = ?', [Str::lower($dto->getTitle())])
            ->where('vehicle_make_id', $dto->getVehicleMakeId())
            ->where('id', '<>', $vehicleModel->id)
            ->first();

        if (!$similar) {
            return;
        }

        throw new NotUniqVehicleModelException($similar);
    }

    public function delete(VehicleModel $vehicleModel): bool
    {
        $vehicleModel->load('vehicles');

        if ($vehicleModel->vehicles->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $vehicleModel->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return VehicleModel::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return VehicleModel::whereKey($ids)->get();
    }

    /**
     * @param iterable<VehicleModel> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
