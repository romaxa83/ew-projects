<?php

namespace Tests\Unit\Parsers;

use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\VehicleDB\VehicleMake;
use App\Services\Parsers\PdfNormalizeService;
use App\Services\Parsers\PdfService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use ReflectionClass;
use Tests\TestCase;
use Throwable;

abstract class BaseParserTest extends TestCase
{
    use DatabaseTransactions;

    public const PICKUP_KEY = 'pickup';
    public const DELIVERY_KEY = 'delivery';
    public const SHIPPER_KEY = 'shipper';

    private const KEYS = [
        self::PICKUP_KEY,
        self::DELIVERY_KEY,
        self::SHIPPER_KEY
    ];

    private const DIR = 'tests/Data/Files/Pdf/';

    protected array $timezones = [];
    protected array $states = [];
    protected array $cities = [];

    private PdfService $service;
    private PdfNormalizeService $normalizeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(PdfService::class);
        $this->normalizeService = resolve(PdfNormalizeService::class);
    }

    /**
     * @param ...$makes
     * @return static
     */
    protected function createMakes(...$makes): self
    {
        foreach ($makes as $make) {
            factory(VehicleMake::class)->create(['name' => $make]);
        }

        return $this;
    }

    /**
     * @param string ...$states
     * @return static
     */
    protected function createStates(?string... $states): self
    {
        $this->states = [];
        $created = [];
        for ($i = 0; $i < count(self::KEYS); $i++) {
            if (!array_key_exists($i, $states)) {
                $this->states[self::KEYS[$i]] = $this->states[self::KEYS[$i-1]];
                continue;
            }
            if ($states[$i] === null) {
                $this->states[self::KEYS[$i]] = null;
                continue;
            }
            if (array_key_exists($states[$i], $created)) {
                $this->states[self::KEYS[$i]] = $this->states[$created[$states[$i]]];
                continue;
            }
            $this->states[self::KEYS[$i]] = factory(State::class)->create(['state_short_name' => $states[$i]])->id;
            $created[$states[$i]] = self::KEYS[$i];
        }
        return $this;
    }

    /**
     * @param string ...$zips
     * @return $this
     */
    protected function createTimeZones(string... $zips): self
    {
        $this->timezones = [];
        $created = [];
        for ($i = 0; $i < count(self::KEYS); $i++) {
            if (!array_key_exists($i, $zips)) {
                $this->timezones[self::KEYS[$i]] = $this->timezones[self::KEYS[$i-1]];
                $this->cities[self::KEYS[$i]] = $this->cities[self::KEYS[$i-1]];
                continue;
            }
            if ($zips[$i] === null) {
                $this->timezones[self::KEYS[$i]] = null;
                $this->cities[self::KEYS[$i]] = null;
                continue;
            }
            if (array_key_exists($zips[$i], $created)) {
                $this->timezones[self::KEYS[$i]] = $this->timezones[$created[$zips[$i]]];
                $this->cities[self::KEYS[$i]] = $this->cities[$created[$zips[$i]]];
                continue;
            }
            $this->cities[self::KEYS[$i]] = $this->faker->city;
            $this->timezones[self::KEYS[$i]] = factory(City::class)
                ->create(
                    [
                        'name' => $this->cities[self::KEYS[$i]],
                        'zip' => $zips[$i],
                        'state_id' => $this->states[self::KEYS[$i]],
                    ]
                )
                ->timezone;
            $created[$zips[$i]] = self::KEYS[$i];
        }
        return $this;
    }

    private function makeFile(string $number): UploadedFile
    {
        $reflection = new ReflectionClass(static::class);
        $dispatcher = preg_replace("/ParserTest/", "", $reflection->getShortName());
        $file = Str::snake($dispatcher) . '_' . $number . '.pdf';
        return UploadedFile::fake()
            ->createWithContent(
                $file,
                file_get_contents(base_path(self::DIR . $dispatcher . '/') . $file)
            );
    }

    /**
     * @param int $number
     * @param array $expected
     * @return void
     * @throws Throwable
     */
    public function assertParsing(int $number, array $expected): void
    {
        $file = $this
            ->makeFile($number);
        $parsed = $this
            ->normalizeService
            ->normalizeAfterParsing(
                $this->service->process($file)
            );
        $this->assertEquals($expected, $parsed);
    }
}
