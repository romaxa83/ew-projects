<?php

namespace App\Imports\States;

use App\Models\Locations\IpRange;
use App\ValueObjects\Point;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class IpRangeImport implements ToModel, WithChunkReading, WithBatchInserts
{
    public const CHUNK_SIZE = 10000;

    public function batchSize(): int
    {
        return self::CHUNK_SIZE;
    }

    public function chunkSize(): int
    {
        return self::CHUNK_SIZE;
    }

    public function model(array $row): IpRange
    {
        return new IpRange(
            [
                'ip_from' => $row[0],
                'ip_to' => $row[1],
                'state' => $row[4],
                'city' => $row[5],
                'coordinates' => new Point($row[7], $row[6]),
                'zip' => $row[8],
            ]
        );
    }
}
