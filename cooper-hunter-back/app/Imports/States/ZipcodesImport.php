<?php

namespace App\Imports\States;

use App\Models\Locations\State;
use App\Models\Locations\Zipcode;
use App\ValueObjects\Point;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ZipcodesImport implements ToModel, WithChunkReading, WithBatchInserts, WithHeadingRow
{
    public const SIZE = 1000;

    public function chunkSize(): int
    {
        return self::SIZE;
    }

    public function model(array $row): ?Zipcode
    {
        if (
            is_null(
            //safe query as State model is cacheable
                $state = State::query()->where('short_name', $row['state_id'])->first()
            )
        ) {
            return null;
        }

        return new Zipcode(
            [
                'state_id' => $state->id,
                'zip' => $row['zip'],
                'coordinates' => new Point($row['lng'], $row['lat']),
                'name' => $row['county_name'],
                'timezone' => $row['timezone'],
            ]
        );
    }

    public function batchSize(): int
    {
        return self::SIZE;
    }
}
