<?php

namespace App\Services\Parsers\Drivers\Rpm\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VehiclesParser extends ValueParserAbstract
{
    private const VEHICLE_PATTERN = "/^(?<vin>[a-z0-9]{17})[\n ]-[\n ](?<year>[0-9]{4})[\n ]-[\n ](?<make>.+?)[\n ]-[\n ](?<model>[^\n]+)\n/is";

    private array $vehicles = [];

    public function parse(string $text): Collection
    {
        $text = $this->replaceBefore($text);

        return $this
            ->splitVehicles($text)
            ->getVehicles();
    }

    private function splitVehicles(string $text): self
    {
        $this->vehicles = preg_split("/\n{2,}/", $text);
        return $this;
    }

    private function getVehicles(): Collection
    {
        $this->vehicles = array_map(
            static fn (string $vehicle): string => preg_replace("/vin:? */i", "", trim($vehicle)),
            $this->vehicles
        );
        $result = [];
        foreach ($this->vehicles as &$vehicle) {
            if (!preg_match(self::VEHICLE_PATTERN, $vehicle, $match)) {
                continue;
            }
            $result[] = [
                'vin' => Str::upper($match['vin']),
                'year' => $match['year'],
                'make' => $match['make'],
                'model' => $match['model']
            ];
        }
        return collect($result);
    }
}
