<?php

namespace App\Imports;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class VehicleMakesAndModelsImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $firstRow = true;
        foreach ($collection as $item) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            $make = VehicleMake::firstOrCreate(
                ['title' => $item[1]],
                [
                    'is_moderated' => true,
                    'vehicle_form' => $this->getVehicleForm($item[0]),
                ],
            );
            VehicleModel::firstOrCreate(
                [
                    'vehicle_make_id' => $make->id,
                    'title' => $item[2],
                ],
                ['is_moderated' => true],
            );
        }
    }

    private function getVehicleForm(string $formName): ?string
    {
        if ($formName === 'Основний ТЗ') {
            return VehicleFormEnum::MAIN;
        }

        if ($formName ==='Причіпний ТЗ') {
            return VehicleFormEnum::TRAILER;
        }

        return null;
    }
}
