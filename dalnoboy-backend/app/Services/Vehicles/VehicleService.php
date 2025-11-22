<?php


namespace App\Services\Vehicles;


use App\Contracts\Models\HasGuard;
use App\Dto\Vehicles\VehicleDto;
use App\Exceptions\Vehicles\IncorrectVehicleDataException;
use App\Exceptions\Vehicles\NotUniqStateNumberException;
use App\Exceptions\Vehicles\NotUniqVinException;
use App\Exceptions\Vehicles\VehicleConnectToInspectionException;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleModel;
use App\Models\Dictionaries\VehicleType;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class VehicleService
{

    public function create(VehicleDto $dto): Vehicle
    {
        return $this->editVehicle($dto, new Vehicle());
    }

    public function update(VehicleDto $dto, Vehicle $vehicle): Vehicle
    {
        return $this->editVehicle($dto, $vehicle);
    }

    public function editVehicle(VehicleDto $dto, Vehicle $vehicle): Vehicle
    {
        $this->checkUniq($dto->getStateNumber(), $dto->getVin(), $vehicle->id);
        $vehicle->state_number = $dto->getStateNumber();
        $vehicle->vin = $dto->getVin();
        $vehicle->form = $dto->getForm();
        $vehicle->class_id = $dto->getClassId();
        $vehicle->type_id = $dto->getTypeId();
        $vehicle->make_id = $dto->getMakeId();
        $vehicle->model_id = $dto->getModelId();
        $vehicle->client_id = $dto->getClientId();
        $vehicle->schema_id = $dto->getSchemaId();
        $vehicle->odo = $dto->getOdo();
        $vehicle->active = $dto->isActive();

        $this->checkRelation($vehicle);

        if (isBackOffice()) {
            $vehicle->is_moderated = $dto->getIsModerated();
        }

        if ($vehicle->isDirty()) {
            if (!isBackOffice()) {
                $vehicle->is_moderated = false;
                // если изменилось только odo, то вернуть модерацию как была (при создании инспекции, у авто меняется только odo, и если она была промодерирована, то модерация слетает)
                if(!$vehicle->isDirty(['state_number', 'vin', 'form', 'class_id', 'type_id', 'make_id', 'model_id', 'client_id', 'schema_id', 'active']) && $vehicle->isDirty('odo')){
                    $vehicle->is_moderated = $vehicle->getOriginal('is_moderated');
                }
            }

            $vehicle->save();
        }
        return $vehicle->refresh();
    }

    private function checkUniq(string $stateNumber, ?string $vin, ?int $id): void
    {
        if (Vehicle::query()->where('state_number', $stateNumber)->where('id', '<>', $id)->exists()) {
            throw new NotUniqStateNumberException();
        }

        if (!$vin) {
            return;
        }

        if (Vehicle::query()->where('vin', $vin)->where('id', '<>', $id)->exists()) {
            throw new NotUniqVinException();
        }
    }

    private function checkRelation(Vehicle $vehicle): void
    {
        if (!VehicleModel::query()
            ->where('id', $vehicle->model_id)
            ->where('vehicle_make_id', $vehicle->make_id)
            ->exists()) {
            throw new IncorrectVehicleDataException();
        }

        if (!VehicleClass::query()
            ->where(VehicleClass::TABLE . '.id', $vehicle->class_id)
            ->where('vehicle_form', $vehicle->form)
            ->first()
            ?->vehicleTypes()
            ->where(VehicleType::TABLE . '.id', $vehicle->type_id)
            ->exists()
        ) {
            throw new IncorrectVehicleDataException();
        }

        if (SchemaVehicle::notDefault()->find($vehicle->schema_id)->vehicle_form->value !== $vehicle->form->value) {
            throw new IncorrectVehicleDataException();
        }
    }

    public function delete(Vehicle $vehicle): bool
    {
        if ($vehicle->inspections()
            ->exists()) {
            throw new VehicleConnectToInspectionException();
        }
        return $vehicle->delete();
    }

    public function show(array $args, array $relations, array $select, HasGuard $user): LengthAwarePaginator
    {
        return Vehicle::activeGuard($user)
            ->filter($args)
            ->select($select)
            ->with($relations)
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }

    /**
     * @param UploadedFile $file
     * @param Vehicle $vehicle
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function saveVehiclePhoto(UploadedFile $file, Vehicle $vehicle): void
    {
        if ($vehicle->getFirstMedia(Vehicle::MC_VEHICLE)?->exists()) {
            return;
        }
        $vehicle
            ->clearMediaCollection(Vehicle::MC_VEHICLE)
            ->copyMedia($file)
            ->toMediaCollection(Vehicle::MC_VEHICLE);
    }

    /**
     * @param UploadedFile $file
     * @param Vehicle $vehicle
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function saveVehicleStateNumberPhoto(UploadedFile $file, Vehicle $vehicle): void
    {
        if ($vehicle->getFirstMedia(Vehicle::MC_STATE_NUMBER)?->exists()) {
            return;
        }
        $vehicle
            ->clearMediaCollection(Vehicle::MC_STATE_NUMBER)
            ->copyMedia($file)
            ->toMediaCollection(Vehicle::MC_STATE_NUMBER);
    }

    public function getByIds(array $ids): Collection
    {
        return Vehicle::whereKey($ids)->get();
    }

    /**
     * @param iterable<Vehicle> $items
     */
    public function toggleActiveMany(iterable $items): void
    {
        foreach ($items as $item) {
            $item->toggleActive()->save();
        }
    }
}
