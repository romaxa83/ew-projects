<?php

namespace App\Services\Parsers\Drivers\Autosled\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^[^\n]+\n(?<address>[^\n]+)\n(?<city>[^,]+), (?<state>[A-Z]{2}) (?<zip>[0-9]+)\n/s";

    private string $text;

    private array $result = [
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phones' => null,
    ];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replacementIntend($this->replaceBefore($text, false)));

        return $this
            ->setPhones()
            ->setLocation()
            ->setFullName()
            ->getCollection();
    }

    private function setPhones(): self
    {
        if (!preg_match("/^Phone: (?<phone>.+)$/m", $this->text, $match)) {
            return $this;
        }
        $phone = preg_replace("/\D/", "", $match['phone']);
        $this->result['phones'] = [
            [
                'number' => $phone
            ]
        ];
        return $this;
    }

    private function setLocation(): self
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text, ['address', 'state', 'city', 'zip'])
        );
        return $this;
    }

    private function setFullName(): self
    {
        if (!preg_match("/^(?<company_name>[^\n]+)\n.+\nContact: (?<contact>[^\n]+)\n/s", $this->text, $match)) {
            return $this;
        }
        $this->result['full_name'] = trim($match['contact'], " \t\n\r\0\x0B.") . ". " . trim($match['company_name']);
        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
