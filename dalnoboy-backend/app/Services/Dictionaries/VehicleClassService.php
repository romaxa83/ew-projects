<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\VehicleClassDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\VehicleClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VehicleClassService
{
    public function create(VehicleClassDto $dto): VehicleClass
    {
        return $this->editVehicleClass($dto, new VehicleClass());
    }

    public function update(VehicleClassDto $dto, VehicleClass $vehicleClass): VehicleClass
    {
        return $this->editVehicleClass($dto, $vehicleClass);
    }

    private function editVehicleClass(VehicleClassDto $dto, VehicleClass $vehicleClass): VehicleClass
    {
        $vehicleClass->vehicle_form = $dto->getVehicleForm();
        $vehicleClass->active = $dto->isActive();
        $vehicleClass->save();

        foreach (languages() as $language) {
            $translation = $dto->getTranslations()[$language->slug]
                ?? $dto->getTranslations()[defaultLanguage()->slug];

            $vehicleClass->translates()
                ->updateOrCreate(
                    [
                        'language' => $language->slug,
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $vehicleClass->refresh();
    }

    public function delete(VehicleClass $vehicleClass): bool
    {
        $vehicleClass->load(['vehicleTypes', 'vehicles']);

        if ($vehicleClass->vehicleTypes->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        if ($vehicleClass->vehicles->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $vehicleClass->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return VehicleClass::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return VehicleClass::whereKey($ids)->get();
    }

    /**
     * @param iterable<VehicleClass> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
