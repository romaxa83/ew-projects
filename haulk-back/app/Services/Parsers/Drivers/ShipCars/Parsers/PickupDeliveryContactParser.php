<?php

namespace App\Services\Parsers\Drivers\ShipCars\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^Address\s+(?<address>.+),\s+(?<city>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/m";

    private string $text;

    private array $result = [
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phones' => null,
        'fax' => null
    ];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replacementIntend($this->replaceBefore($text)));

        return $this
            ->setFullName()
            ->setLocation()
            ->setPhones()
            ->getCollection();
    }

    private function setFullName(): self
    {
        preg_match("/^Name\s*(?<name>.+)/m", $this->text, $match);
        if (!empty($match['name'])) {
            $name = trim($match['name']);
            if (!empty($name)) {
                $names[] = $name;
            }
        }
        preg_match("/^Company\s*(?<company>[^\n]+)\n/s", $this->text, $match);
        if (!empty($match['company'])) {
            $name = trim($match['company']);
            if (!empty($name)) {
                $names[] = $name;
            }
        }
        if (empty($names)) {
            return $this;
        }

        $this->result['full_name'] = implode(". ", $names);

        return $this;
    }

    private function setLocation(): self
    {
        $text = preg_replace(
            "/\n/",
            " ",
            preg_replace(
                "/.+\n(Address )/s",
                "$1",
                preg_replace(
                    "/\nPhone {2,}.+/s",
                    "",
                    $this->text
                )
            )
        );
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $text, ['address', 'city', 'state', 'zip'])
        );
        return $this;
    }

    private function setPhones(): self
    {
        preg_match("/^Phone\s+(?<phones>.+)$/m", $this->text, $match);
        if (empty($match['phones'])) {
            return $this;
        }
        $phones = trim($match['phones']);
        if (empty($phones)) {
            return $this;
        }
        $phones = explode(";", $phones);
        foreach ($phones as $phone) {
            $phone = preg_replace("/\D/", "", $phone);
            if (empty($phone)) {
                continue;
            }
            $this->result['phones'][] = [
                'number' => $phone
            ];
        }
        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
