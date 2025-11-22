<?php


namespace App\Imports;


use App\Exceptions\Timezone\IncorrectTimezoneCountryException;
use App\Exceptions\Timezone\IncorrectTimezoneException;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Services\TimezoneService;
use Exception;
use Illuminate\Support\Collection;
use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CanadaCitiesImport implements ToCollection, WithHeadingRow, WithChunkReading
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
        return 3000;
    }

    public function collection(Collection $collection)
    {
        $timezone = resolve(TimezoneService::class);
        try {
            $collection = $collection->filter(
                function (&$item) use ($timezone) {
                    if (empty($this->states[$item['province_abbr']])) {
                        return false;
                    }
                    try {
                        $tz = $timezone->getTimezoneToImport($item['province_abbr'], false);

                        if ($tz['found'] === false) {
                            return false;
                        }

                        $item['timezone'] = $tz['timezone'];

                        return true;
                    } catch (IncorrectTimezoneException | IncorrectTimezoneCountryException $e) {
                       return false;
                    }
                }
            );

            $collection = $collection->unique(
                function ($item) {
                    return $item['city'] . $item['postal_code'];
                }
            );
            $collection->transform(
                function ($item) use ($timezone) {
                    return [
                        'name' => $item['city'],
                        'zip' => $item['postal_code'],
                        'status' => true,
                        'state_id' => $this->states[$item['province_abbr']] ?? 0,
                        'timezone' => $item['timezone'],
                        'country_code' => 'ca',
                        'country_name' => 'Canada',
                    ];
                }
            );

            City::upsert($collection->toArray(), ['name', 'zip', 'country_code']);
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
