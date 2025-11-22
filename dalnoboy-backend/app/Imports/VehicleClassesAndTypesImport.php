<?php

namespace App\Imports;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class VehicleClassesAndTypesImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $defaultLang = defaultLanguage()->slug;

        foreach ($collection as $item) {
            $vehicleClass = $this->findOrCreateVehicleClass($item[0], $item[1], $defaultLang);
            $this->findOrCreateVehicleType($vehicleClass, $item[2], $defaultLang);
        }
    }

    private function findOrCreateVehicleClass(string $vehicleFormName, string $vehicleClassName, string $defaultLang): VehicleClass
    {
        $vehicleForm = $this->getVehicleForm($vehicleFormName);

        $vehicleClass = VehicleClass::query()
            ->where('vehicle_form', $vehicleForm)
            ->whereHas('translates', function (Builder $b) use ($defaultLang, $vehicleClassName) {
                $b->where('language', $defaultLang)
                    ->where('title',$vehicleClassName);
            })
            ->first();

        if ($vehicleClass === null) {
            $vehicleClass = VehicleClass::create(['vehicle_form' => $vehicleForm]);
            foreach (languages() as $lang) {
                $vehicleClass->translates()
                    ->firstOrCreate(
                        [
                            'language' => $lang->slug,
                        ],
                        [
                            'title' => $vehicleClassName,
                        ]
                    );
            }
        }

        return $vehicleClass;
    }

    private function findOrCreateVehicleType(VehicleClass $vehicleClass, string $vehicleTypeName, string $defaultLang): VehicleClass
    {
        $vehicleType = VehicleType::query()
            ->whereHas('translates', function (Builder $b) use ($defaultLang, $vehicleTypeName) {
                $b->where('language', $defaultLang)
                    ->where('title',$vehicleTypeName);
            })
            ->first();

        if ($vehicleType === null) {
            $vehicleType = VehicleType::create();
            foreach (languages() as $lang) {
                $vehicleType->translates()
                    ->firstOrCreate(
                        [
                            'language' => $lang->slug,
                        ],
                        [
                            'title' => $vehicleTypeName,
                        ]
                    );
            }
        }

        if ($vehicleType->vehicleClasses()->find($vehicleClass->id) === null) {
            $vehicleType->vehicleClasses()->attach($vehicleClass->id);
        }

        return $vehicleClass;
    }

    private function getVehicleForm(string $formName): string
    {
        if ($formName === 'Основний ТЗ') {
            return VehicleFormEnum::MAIN;
        }

        return VehicleFormEnum::TRAILER;
    }
}
