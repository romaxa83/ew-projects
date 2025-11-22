<?php


namespace App\Services;


use App\Exceptions\Timezone\IncorrectTimezoneCountryException;
use App\Exceptions\Timezone\IncorrectTimezoneException;
use Carbon\CarbonTimeZone;
use DateTimeZone;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TimezoneService
{
    private const COUNTRY_USA = 'USA';
    private const COUNTRY_CANADA = 'Canada';

    private const COUNTRY_CODE = [
        'US' => self::COUNTRY_USA,
        'CA' => self::COUNTRY_CANADA,
        'PR' => self::COUNTRY_USA,
        'UM' => self::COUNTRY_USA
    ];

    private const COUNTRY_SHORT_CODE = [
        self::COUNTRY_USA => 'US',
        self::COUNTRY_CANADA => 'CA'
    ];

    private const TIMEZONES = [
        self::COUNTRY_USA => [
            'America/Puerto_Rico',//UTC-4 Atlantic Standard Time (AST)
            'America/New_York', //UTC-4 Eastern Daylight Time (EDT)
            'America/Chicago', //UTC-5 Central Daylight Time (CDT)
            'America/Denver', //UTC-6 Mountain Daylight Time (MDT)
            'America/Phoenix', //UTC-7 Mountain Standard Time (MST)
            'America/Los_Angeles', //UTC-7 Pacific Daylight Time (PDT)
            'America/Anchorage', //UTC-8 Alaskan Daylight Time (AKDT)
            'America/Adak', //UTC-09 Hawaii Standard Time (HST)
            'Pacific/Honolulu', //UTC-10 Hawaii Standard Time (HST)
            'Pacific/Midway',//UTC-11 Samoa Standard Time (SST)
        ],
        self::COUNTRY_CANADA => [
            'America/St_Johns',//UTC-02:30 Newfoundland Daylight Time, NDT
            'America/Halifax',//UTC-03:00 Atlantic Daylight Time, ADT
            'America/Toronto',//UTC-04:00 Eastern Daylight Time, EDT
            'America/Winnipeg',//UTC-05:00 Central Daylight Time, CDT
            'America/Edmonton',//UTC-06:00 Mountain Daylight Time, MDT
            'America/Vancouver',//UTC-07:00 Pacific Daylight Time, PDT
            'America/Atikokan',//UTC-05:00 Eastern Standard Time, EST
            'America/Regina',//UTC-06:00 Central Standard Time, CST
            'America/Creston'//UTC-07:00 Mountain Standard Time, MST
        ]
    ];

    private const DST_MONTH = '08';

    public const TIMEZONE_ABBR = [
        'NST' => 'Newfoundland Standard Time, NST',
        'NDT' => 'Newfoundland Daylight Time, NDT',
        'NDDT' => 'Newfoundland Daylight Time, NDT',

        'AST' => 'Atlantic Standard Time, AST',
        'ADT' => 'Atlantic Daylight Time, ADT',

        'EST' => 'Eastern Standard Time, EST',
        'EDT' => 'Eastern Daylight Time, EDT',

        'CST' => 'Central Standard Time, CST',
        'CDT' => 'Central Daylight Time, CDT',

        'MST' => 'Mountain Standard Time, MST',
        'MDT' => 'Mountain Daylight Time, MDT',

        'PST' => 'Pacific Standard Time, PST',
        'PDT' => 'Pacific Daylight Time, PDT',

        'AKST' => 'Alaskan Standard Time, AKST',
        'AHST' => 'Alaska-Hawaii Standard Time, AHKST',
        'AKDT' => 'Alaskan Daylight Time, AKDT',
        'AHDT' => 'Alaska-Hawaii Daylight Time, AHDT',

        'BST' => 'Hawaii-Aleutian Standard Time, HAST',
        'BDT' => 'Hawaii-Aleutian Daylight Time, HADT',

        'HST' => 'Hawaii Standard Time, HST',
        'HDT' => 'Hawaii Standard Time, HST',

        'SST' => 'Samoa Standard Time, SST',//Isn't SDT
    ];

    private static Collection $timezonesArr;

    private static Collection $timezoneToStates;

    private static array $newTimezoneFormat = [];

    private static array $timezoneImport = [];

    private ?string $country = null;

    public function isValidTimezone(string $timezoneName): bool
    {
        if (!$timezoneName) {
            return false;
        }

        $tz = new CarbonTimeZone($timezoneName);

        return $tz && $tz->getName() === $timezoneName;
    }


    public function getTimezonesArr(): Collection
    {
        if (!empty(self::$timezonesArr)) {
            return self::$timezonesArr;
        }

        self::$timezonesArr = collect();

        $myTimeZone = date_default_timezone_get();

        foreach (self::TIMEZONES as $country => $timezones) {

            foreach ($timezones as $timezone) {

                self::$timezonesArr[] = $this->createTimezoneDescription($timezone, $country);
            }
        }

        date_default_timezone_set($myTimeZone);

        return self::$timezonesArr;
    }

    /**
     * @param string $timezone
     * @param string|null $country
     * @return array
     * @throws IncorrectTimezoneCountryException
     * @throws IncorrectTimezoneException
     */
    private function createTimezoneDescription(string $timezone, ?string $country = null): array
    {
        try {
            date_default_timezone_set($timezone);
        } catch (Exception $e) {
            throw new IncorrectTimezoneException();
        }
        //Get timezone abbr in DST time (some timezone have no DST time)
        $dstDay = strtotime(date('Y') . self::DST_MONTH . '01');

        $abbr = date('T', $dstDay);
        $abbrDescription = self::TIMEZONE_ABBR[$abbr] ?? $timezone;

        $offset = date('P', $dstDay);

        if ($country === null) {
            if (!empty($this->country)) {
                $country = $this->country;
            } else {
                $tz = new DateTimeZone($timezone);

                $location = $tz->getLocation();

                if ($location['country_code'] !== '??' && !array_key_exists($location['country_code'], self::COUNTRY_CODE)) {
                    throw new IncorrectTimezoneCountryException();
                }
                $country =  self::COUNTRY_CODE[$location['country_code']];
            }
        }

        return [
            'timezone' => $timezone,
            'country' => $country,
            'offset_string' => $offset,
            'offset' => (int)date('Z', $dstDay),
            'abbr' => $abbr,
            'abbr_description' => $abbrDescription,
            'title' => $country . ': UTC ' . $offset . ' ' . $abbrDescription,
            'dst' => date('I', $dstDay) === "1"
        ];
    }

    public function getTimezonesList(): array
    {
        $data = [];

        foreach ($this->getTimezonesArr() as $item) {

            $data[] = [
                'timezone' => $item['timezone'],
                'title' => '(' . $item['country'] . ') UTC ' . $item['offset_string'] . ' ' . $item['abbr_description'],
                'country_code' => $item['country'] === self::COUNTRY_USA ? 'us' : 'ca',
                'country_name' => $item['country'],
            ];
        }

        return $data;
    }

    public function changeOldTimeZoneFormatToNew(string $timezone): array
    {
        if (array_key_exists($timezone, self::$newTimezoneFormat)) {
            return self::$newTimezoneFormat[$timezone];
        }
        $timezones = $this->getTimezonesArr();

        $myTimeZone = date_default_timezone_get();

        $description = $this->createTimezoneDescription($timezone);
        //Return timezone
        date_default_timezone_set($myTimeZone);

        $search = $timezones->filter(
            function (array $item) use ($description) {
                return $item['country'] === $description['country'] &&
                    $item['offset'] === $description['offset'] &&
                    $item['abbr'] === $description['abbr'] &&
                    $item['dst'] === $description['dst'];
            }
        )->first();

        if (!empty($search)) {
            self::$newTimezoneFormat[$timezone] = [
                'timezone' => $search['timezone'],
                'description' => $search['title'],
                'found' => true
            ];
        } else {
            self::$newTimezoneFormat[$timezone] = [
                'timezone' => $timezone,
                'description' => $description['title'],
                'found' => false
            ];
        }

        return self::$newTimezoneFormat[$timezone];
    }

    public function getTimezoneToImport(string $state, bool $isUsa = true): array
    {
        $this->country = $isUsa === true ? self::COUNTRY_USA : self::COUNTRY_CANADA;

        $key = $this->country . $state;

        if (array_key_exists($key, self::$timezoneImport)) {
            return self::$timezoneImport[$key];
        }

        $timezone = $this->getStateTimezone($state);

        if (empty($timezone)) {
            return [
                'timezone' => null,
                'description' => null,
                'found' => false
            ];
        }

        self::$timezoneImport[$key] = $this->changeOldTimeZoneFormatToNew($timezone);

        return self::$timezoneImport[$key];
    }

    private function getStateTimezone(string $state): ?string
    {
        $key = self::COUNTRY_SHORT_CODE[$this->country];

        if (empty(self::$timezoneToStates)) {
            self::$timezoneToStates = collect();
        }

        if (self::$timezoneToStates->has($key)) {
            return self::$timezoneToStates[$key]->has($state) ? self::$timezoneToStates[$key][$state] : null;
        }

        self::$timezoneToStates->put($key, collect());

        $states = collect(Excel::toArray(
            function () {},
            Storage::disk('public')->path('zipcode/time_zone.csv')
        )[0]);

        $states = $states->filter(fn ($item) => $item[0] === $key)->values();

        foreach ($states as $item) {
            self::$timezoneToStates[$key]->put($item[1], $item[2]);
        }

        return $this->getStateTimezone($state);
    }
}
