<?php

namespace App\Imports;

use App\Models\Locations\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RegionsImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $item) {
            $region = Region::firstOrCreate(
                [
                    'slug' => Str::slug($item['en'])
                ]
            );
            foreach ($item as $language => $title) {
                $region->translates()
                    ->updateOrCreate(
                        [
                            'language' => $language,
                        ],
                        [
                            'title' => $title
                        ]
                    );
            }
        }
    }
}
