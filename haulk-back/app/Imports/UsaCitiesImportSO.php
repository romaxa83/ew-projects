<?php


namespace App\Imports;


use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Services\TimezoneService;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsaCitiesImportSO  implements ToCollection, WithHeadingRow, WithChunkReading
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

            $timezone = resolve(TimezoneService::class);

            $collection = $collection->filter(
                function ($item) use ($timezone) {
                    if (empty($this->states[$item['state']])) {
                        return false;
                    }
                    $tz = $timezone->getTimezoneToImport($item['state']);

                    return $tz['found'] !== false;
                }
            );

            $collection = $collection->unique(
                function ($item) {
                    return $item['city'] . $item['zip_code'];
                }
            );

            $collection->transform(
                function ($item) use ($timezone) {
                    return [
                        'name' => mb_convert_case($item['city'], MB_CASE_TITLE),
                        'zip' => $item['zip_code'],
                        'status' => true,
                        'state_id' => $this->states[$item['state']] ?? 0,
                        'timezone' => $timezone->getTimezoneToImport($item['state'])['timezone'],
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
