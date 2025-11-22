<?php

namespace App\Services\Dictionaries;

use App\Contracts\Models\HasGuard;
use App\Dto\Dictionaries\VehicleTypeDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VehicleTypeService
{
    public function create(VehicleTypeDto $dto): VehicleType
    {
        return $this->editVehicleClass($dto, new VehicleType());
    }

    public function update(VehicleTypeDto $dto, VehicleType $vehicleType): VehicleType
    {
        return $this->editVehicleClass($dto, $vehicleType);
    }

    private function editVehicleClass(VehicleTypeDto $dto, VehicleType $vehicleType): VehicleType
    {
        $vehicleType->active = $dto->isActive();
        $vehicleType->save();
        $vehicleClasses = VehicleClass::find($dto->getVehicleClasses());
        $vehicleType->vehicleClasses()->detach();
        $vehicleType->vehicleClasses()->attach($vehicleClasses);

        foreach (languages() as $language) {
            $translation = $dto->getTranslations()[$language->slug]
                ?? $dto->getTranslations()[defaultLanguage()->slug];

            $vehicleType->translates()
                ->updateOrCreate(
                    [
                        'language' => $language->slug,
                    ],
                    [
                        'title' => $translation->getTitle(),
                    ]
                );
        }

        return $vehicleType->refresh();
    }

    public function delete(VehicleType $vehicleType): bool
    {
        $vehicleType->load('vehicles');

        if ($vehicleType->vehicles->isNotEmpty()) {
            throw new HasRelatedEntitiesException();
        }

        return $vehicleType->delete();
    }

    public function show(array $args, array $relation, array $select, HasGuard $user): LengthAwarePaginator
    {
        return VehicleType::filter($args)
            ->activeGuard($user)
            ->select($select)
            ->with($relation)
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }

    public function getByIds(array $ids): Collection
    {
        return VehicleType::whereKey($ids)->get();
    }

    /**
     * @param iterable<VehicleType> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
