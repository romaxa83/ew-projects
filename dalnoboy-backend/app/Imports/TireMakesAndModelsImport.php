<?php

namespace App\Imports;

use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TireMakesAndModelsImport implements ToCollection
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
            $make = TireMake::firstOrCreate(
                ['title' => $item[0]],
                ['is_moderated' => true],
            );
            TireModel::firstOrCreate(
                [
                    'tire_make_id' => $make->id,
                    'title' => $item[1],
                ],
                ['is_moderated' => true],
            );
        }
    }
}
