<?php


namespace App\Imports;


use App\Models\Locations\State;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsaStatesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function chunkSize(): int
    {
        return 100;
    }

    public function collection(Collection $collection): void
    {
        try {
            $collection->transform(
                function ($item) {
                    $item['status'] = true;
                    $item['country_code'] = 'us';
                    $item['country_name'] = 'USA';

                    return $item;
                }
            );

            State::query()->upsert($collection->toArray(), ['name', 'country_code']);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
