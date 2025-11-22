<?php

namespace App\Imports\States;

use App\Models\Locations\State;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class StateImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row): void
    {
        $rowArr = $this->rowToArray($row);

        if (empty($rowArr['short_name'])) {
            return;
        }

        $state = State::query()->firstOrCreate(
            [
                'short_name' => $rowArr['short_name']
            ],
            $rowArr
        );

        foreach (languages() as $language) {
            $state->translations()->updateOrCreate(
                [
                    'name' => $rowArr['name'],
                    'language' => $language->slug,
                ]
            );
        }
    }

    protected function rowToArray(Row $row): array
    {
        $rowArr = $row->toArray();

        return [
            'name' => $rowArr['state'],
            'short_name' => $rowArr['code'],
            'status' => true,
            'hvac_license' => (bool)$rowArr['require_hvac_license'],
            'epa_license' => (bool)$rowArr['require_epa'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
