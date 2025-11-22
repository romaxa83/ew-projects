<?php


namespace App\Services\Parsers\Drivers\WholesaleExpress2\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{

    private const PATTERN_FULL_NAME = "/^(?<full_name>[^\n]+)\n/is";
    private const PATTERN_ADDRESS = "/^[^\n]+\n(?<address>[^\n]+)\n/is";
    private const PATTERN_LOCATION = "/^[^\n]+\n[^\n]+\n(?<city>[^\,]+),*\s*(?<state>[a-z]{2})\s*(?<zip>[0-9]+)\n/is";
    private const PATTERN_PHONE = "/Tel:(?<phone>[^\n]+)\n/is";
    private const PATTERN_FAX = "/Fax:(?<fax>[^\n$]+)(\n|$)/is";

    private string $text;

    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null,
        'fax' => null
    ];

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text);

        return $this
            ->setFullName()
            ->setAddress()
            ->setLocation()
            ->setPhone()
            ->setFax()
            ->getResult();
    }

    private function setFullName(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_FULL_NAME, $this->text, $match)) {
            return $this;
        }

        $fullName = trim(data_get($match, 'full_name'));

        if (empty($fullName)) {
            return $this;
        }
        $this->result['full_name'] = $fullName;
        return $this;
    }

    private function setAddress(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_ADDRESS, $this->text, $match)) {
            return $this;
        }

        $this->result['address'] = trim($match['address'], " \t\n\r\0\x0B.");
        return $this;
    }

    private function setLocation(): ShipperContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text)
        );
        return $this;
    }

    private function setPhone(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_PHONE, $this->text, $match)) {
            return $this;
        }
        $this->result['phones'] = [
            [
                'number' => preg_replace("/[^0-9]+/", "", $match['phone'])
            ]
        ];
        return $this;
    }

    private function setFax(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_FAX, $this->text, $match)) {
            return $this;
        }

        $this->result['fax'] = preg_replace("/[^0-9]+/", "", $match['fax']);

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
