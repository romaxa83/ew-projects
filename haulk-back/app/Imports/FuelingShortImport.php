<?php


namespace App\Imports;


use App\Models\Locations\State;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FuelingShortImport implements ToArray, WithHeadingRow
{
    private int $rows = 0;

    public function array(array $array)
    {
        $this->rows = count($array);
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
