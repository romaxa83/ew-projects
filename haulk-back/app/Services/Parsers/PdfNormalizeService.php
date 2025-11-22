<?php

namespace App\Services\Parsers;

use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Services\Vehicles\VinDecodeService;
use Illuminate\Support\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log;
use Throwable;

class PdfNormalizeService
{
    private VinDecodeService $vinDecodeService;

    public function __construct(VinDecodeService $vinDecodeService)
    {
        $this->vinDecodeService = $vinDecodeService;
    }

    /**
     * @param $data
     * @return array
     * @throws Throwable
     */
    public function normalizeAfterParsing($data): array
    {
        return $this->postprocessData($data);
    }

    /**
     * @param Collection $parsedOrder
     * @return array
     * @throws Throwable
     */
    private function postprocessData(Collection $parsedOrder): array
    {
        $parsedOrderArray = $parsedOrder->toArray();

        if (!empty($parsedOrderArray['pickup_date']) && is_array($parsedOrderArray['pickup_date'])) {
            $date = $parsedOrderArray['pickup_date'];
            $parsedOrderArray['pickup_date'] = $date['date'] ?? '';
            $parsedOrderArray['pickup_time'] = $date['time_interval'] ?? null;
        } elseif (!empty($parsedOrderArray['pickup_contact']['pickup_date'])) {
            $parsedOrderArray['pickup_date'] = $parsedOrderArray['pickup_contact']['pickup_date'];
            unset($parsedOrderArray['pickup_contact']['pickup_date']);
        } else {
            $parsedOrderArray['pickup_date'] = $this->normalizeDate($parsedOrderArray['pickup_date'] ?? '');
        }

        if (!empty($parsedOrderArray['delivery_date']) && is_array($parsedOrderArray['delivery_date'])) {
            $date = $parsedOrderArray['delivery_date'];
            $parsedOrderArray['delivery_date'] = $date['date'] ?? '';
            $parsedOrderArray['delivery_time'] = $date['time_interval'] ?? null;
        } elseif (!empty($parsedOrderArray['delivery_contact']['delivery_date'])) {
            $parsedOrderArray['delivery_date'] = $parsedOrderArray['delivery_contact']['delivery_date'];
            unset($parsedOrderArray['delivery_contact']['delivery_date']);
        } else {
            $parsedOrderArray['delivery_date'] = $this->normalizeDate($parsedOrderArray['delivery_date'] ?? '');
        }

        $parsedOrderArray['vehicles'] = $this->normalizeVehicles($parsedOrderArray['vehicles'] ?? []);

        if (isset($parsedOrderArray['pickup_contact'])) {
            $parsedOrderArray['pickup_contact'] = $this->normalizeContact($parsedOrderArray['pickup_contact']);
        }

        if (isset($parsedOrderArray['delivery_contact'])) {
            $parsedOrderArray['delivery_contact'] = $this->normalizeContact($parsedOrderArray['delivery_contact']);
        }

        if (isset($parsedOrderArray['shipper_contact'])) {
            $parsedOrderArray['shipper_contact'] = $this->normalizeContact($parsedOrderArray['shipper_contact']);
        }

        if (array_key_exists('instructions', $parsedOrderArray) && is_string($parsedOrderArray['instructions'])) {
            $parsedOrderArray['instructions'] = trim($parsedOrderArray['instructions']);
            if (empty($parsedOrderArray['instructions'])) {
                $parsedOrderArray['instructions'] = null;
            }
        }

        if (!empty($parsedOrderArray['pickup_contact']['instruction'])) {
            $parsedOrderArray['dispatch_instructions'] = (empty($parsedOrderArray['dispatch_instructions']) ? "" : $parsedOrderArray['dispatch_instructions'] . "\n")
                . "Pickup: " . $parsedOrderArray['pickup_contact']['instruction'];
            unset($parsedOrderArray['pickup_contact']['instruction']);
        }

        if (!empty($parsedOrderArray['delivery_contact']['instruction'])) {
            $parsedOrderArray['dispatch_instructions'] = (empty($parsedOrderArray['dispatch_instructions']) ? "" : $parsedOrderArray['dispatch_instructions'] . "\n")
                . "Delivery: " . $parsedOrderArray['delivery_contact']['instruction'];
            unset($parsedOrderArray['delivery_contact']['instruction']);
        }

        return $parsedOrderArray;
    }

    private function normalizeDate(string $date): string
    {
        if (empty($date)) {
            return $date;
        }
        try {
            return Carbon::parse($date)->format("m/d/Y");
        } catch (InvalidFormatException $e) {
            return "";
        }
    }

    /**
     * @param array $vehicles
     * @return array
     * @throws Throwable
     */
    private function normalizeVehicles(array $vehicles): array
    {
        if (!count($vehicles)) {
            return $vehicles;
        }

        return array_map(
            function ($vehicle) {
                $vehicle['inop'] = false;
                $vehicle['enclosed'] = false;

                if (empty($vehicle['vin'])) {
                    return $vehicle;
                }

                if (!preg_match("/^[a-z0-9]{17}$/i", $vehicle['vin']) || preg_match("/^(.)\g1+$/", $vehicle['vin'])) {
                    $vehicle['vin'] = null;
                    return $vehicle;
                }

                try {
                    $vinData = $this->vinDecodeService->decodeVin($vehicle['vin']);

                    $vehicle['vin'] = mb_strtoupper($vehicle['vin']);
                    $vehicle['make'] = $vehicle['make'] ?? $vinData['make'];
                    $vehicle['model'] = $vehicle['model'] ?? $vinData['model'];
                    $vehicle['year'] = $vehicle['year'] ?? $vinData['year'];
                    $vehicle['type_id'] = $vinData['type_id'] ?? null;
                } catch (Exception $e) {
                    Log::error($e);
                }

                return $vehicle;
            },
            $vehicles
        );
    }

    private function normalizeContact(array $contact): array
    {
        if ($this->hasPhones($contact)) {
            $contact['phones'] = $this->normalizePhones($contact['phones']);

            $phone = array_shift($contact['phones']);

            $contact['phone'] = $phone['number'] ?? null;
            $contact['phone_name'] = $phone['phone_name'] ?? null;
            $contact['phone_extension'] = $phone['phone_extension'] ?? null;
        }

        if ($this->hasFax($contact)) {
            $contact['fax'] = $this->normalizePhone($contact['fax']);
        }

        if (isset($contact['state'])) {
            $contact['state_id'] = $this->convertToStateId($contact['state']);
        }
        if (!isset($contact['zip'])) {
            return $contact;
        }
        $city = $this->findCityByZip($contact['zip']);
        $contact['timezone'] = optional($city)->timezone ?? '';
        if (empty($city)) {
            return $contact;
        }
        if (!empty($contact['city'])) {
            return $contact;
        }
        $contact['city'] = $city->name;

        return $contact;
    }

    private function hasPhones(array $contact): bool
    {
        return isset($contact['phones'])
            && $contact['phones']
            && count($contact['phones']);
    }

    private function normalizePhones($phones): array
    {
        $result = [];

        foreach ($phones as $phone) {
            if (isset($phone['number']) && !preg_match('/^0+$/', $phone['number'])) {
                $phone['number'] = $this->normalizePhone($phone['number']);

                $result[] = $phone;
            }
        }

        return $result;
    }

    private function normalizePhone(string $phone): string
    {
        if (Str::length($phone) === 10) {
            return config('phones.prefixes.default') . $phone;
        }

        return $phone;
    }

    private function hasFax(array $contact): bool
    {
        return !empty($contact['fax']);
    }

    private function convertToStateId(string $stateName): ?int
    {
        if ($state = State::where('state_short_name', $stateName)->first()) {
            return $state->id;
        }

        return null;
    }

    private function findCityByZip($zip): ?City
    {
        return City::where('zip', $zip)->limit(1)->first();
    }
}
