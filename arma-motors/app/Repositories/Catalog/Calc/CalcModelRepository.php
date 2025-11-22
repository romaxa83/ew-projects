<?php

namespace App\Repositories\Catalog\Calc;

use App\DTO\Catalog\Calc\Model\CalcModelDTO;
use App\Models\Catalogs\Calc\CalcModel;
use App\Repositories\AbstractRepository;

class CalcModelRepository extends AbstractRepository
{
    public function query()
    {
        return CalcModel::query();
    }

    public function existModelByDTO(CalcModelDTO $dto, $withoutId = null): bool
    {
        $query = $this->query()
            ->where('model_id', $dto->getModelId())
            ->where('brand_id', $dto->getBrandId())
            ->where('mileage_id', $dto->getMileageId())
            ->where('transmission_id', $dto->getTransmissionId())
            ->where('fuel_id', $dto->getFuelId())
            ->where('drive_unit_id', $dto->getDriveUnitId())
            ->where('engine_volume_id', $dto->getEngineVolumeId());

        if($withoutId){
            $query->where('id', '!=', $withoutId);
        }

        return $query->exists();
    }

    public function getModelByDTO(CalcModelDTO $dto)
    {
        return $this->query()
            ->with([
                'model',
                'brand',
                'works',
                'works.current',
                'spares',
                'spares.group',
            ])
            ->where('model_id', $dto->getModelId())
            ->where('brand_id', $dto->getBrandId())
            ->where('mileage_id', $dto->getMileageId())
            ->where('transmission_id', $dto->getTransmissionId())
            ->where('fuel_id', $dto->getFuelId())
            ->where('drive_unit_id', $dto->getDriveUnitId())
            ->where('engine_volume_id', $dto->getEngineVolumeId())
            ->first()
            ;
    }
}
