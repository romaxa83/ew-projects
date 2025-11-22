<?php

namespace App\Imports;

use App\Models\Locations\City;
use App\Models\Locations\State;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsaCitiesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private array $states = [];

    public function __construct()
    {
        $states = State::get();

        if ($states->count()) {
            foreach ($states as $state) {
                $this->states[$state->state_short_name] = $state->id;
            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function collection(Collection $collection)
    {
        try {
            $collection = $collection->filter(
                function ($item) {
                    return isset($this->states[$item['state']]);
                }
            );

            $collection = $collection->unique(
                function ($item) {
                    return $item['primary_city'] . $item['zip'];
                }
            );

            $collection->transform(
                function ($item) {
                    return [
                        'name' => $item['primary_city'],
                        'zip' => $item['zip'],
                        'status' => true,
                        'state_id' => $this->states[$item['state']] ?? 0,
                        'timezone' => $item['timezone'],
                        'country_code' => 'us',
                        'country_name' => 'USA',
                    ];
                }
            );

            City::query()->upsert($collection->toArray(), ['name', 'zip', 'country_code']);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
