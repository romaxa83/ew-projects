<?php

namespace App\Services\Parsers\Drivers\Rpm\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^(?<address>.+),\s+(?<city>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/m";

    private string $text;

    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null,
        'email' => null
    ];

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text);

        return $this
            ->setLocation()
            ->setPhone()
            ->setFullName()
            ->getResult();
    }

    private function setLocation(): self
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text, ['address', 'state', 'zip', 'city'])
        );
        $this->text = preg_replace("/^[^\n]+\n/s", "", $this->text);
        return $this;
    }

    private function setPhone(): self
    {
        if (!preg_match("/Phone:\s*(?<phone>.+)$/m", $this->text, $match)) {
            return $this;
        }
        $phone = preg_replace("/\D/", "", $match['phone']);
        if (empty($phone)) {
            return $this;
        }
        $this->result['phones'][] = [
            'number' => $phone
        ];
        return $this;
    }

    private function setFullName(): self
    {
        $names = [];
        if (preg_match("/Contact: (?<contact>.+) •/", $this->text, $match)) {
            $names[] = $match['contact'];
        }
        $names[] = 'RPM';
        $this->result['full_name'] = implode(". ", $names);
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
