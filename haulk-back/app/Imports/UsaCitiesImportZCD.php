<?php


namespace App\Imports;


use App\Exceptions\Timezone\IncorrectTimezoneCountryException;
use App\Exceptions\Timezone\IncorrectTimezoneException;
use App\Models\Locations\State;
use App\Services\TimezoneService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsaCitiesImportZCD  implements ToCollection, WithHeadingRow, WithChunkReading
{

    private const TYPE_MILITARY = 'MILITARY';

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
        try {

            $timezone = resolve(TimezoneService::class);

            $collection = $collection->filter(
                function (&$item) use ($timezone) {
                    if ($item['type']  === self::TYPE_MILITARY || empty($this->states[$item['state']])) {
                        return false;
                    }

                    if (!empty($item['timezone'])) {
                        try {
                            $tzFromOldTz = $timezone->changeOldTimeZoneFormatToNew($item['timezone']);
                        } catch (IncorrectTimezoneException $e) {

                        }
                    }

                    if (!empty($tzFromOldTz)) {
                        $tz = $tzFromOldTz;
                    } else {
                        try {
                            $tz = $timezone->getTimezoneToImport($item['state']);
                        } catch (IncorrectTimezoneException | IncorrectTimezoneCountryException $e) {
                            $tz = ['found' => false];
                        }
                    }

                    if ($tz['found'] === false) {
                        return false;
                    }

                    $item['timezone'] = $tz['timezone'];

                    return true;
                }
            );

            $collection = $collection->unique(
                function ($item) {
                    return $item['primary_city'] . $item['zip'];
                }
            );

            $collection->transform(
                function ($item) use ($timezone) {
                    return [
                        'name' => mb_convert_case($item['primary_city'], MB_CASE_TITLE),
                        'zip' => $item['zip'],
                        'status' => true,
                        'state_id' => $this->states[$item['state']],
                        'timezone' => $item['timezone'],
                        'country_code' => 'us',
                        'country_name' => 'USA',
                    ];
                }
            );
            DB::table('cities_temp')->insert($collection->toArray());
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
