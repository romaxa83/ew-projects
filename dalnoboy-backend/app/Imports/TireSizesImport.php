<?php

namespace App\Imports;

use App\Models\Dictionaries\TireDiameter;
use App\Models\Dictionaries\TireHeight;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireWidth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TireSizesImport implements ToCollection
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
            $diameter = TireDiameter::firstOrCreate([
                'value' => $item[1],
            ]);
            $width = TireWidth::firstOrCreate([
                'value' => (float)str_replace(',', '.', $item[2]),
            ]);
            $height = TireHeight::firstOrCreate([
                'value' => (float)str_replace(',', '.', $item[3]),
            ]);
            TireSize::firstOrCreate(
                [
                    'tire_diameter_id' => $diameter->id,
                    'tire_width_id' => $width->id,
                    'tire_height_id' => $height->id,
                ],
                ['is_moderated' => true],
            );
        }
    }
}
