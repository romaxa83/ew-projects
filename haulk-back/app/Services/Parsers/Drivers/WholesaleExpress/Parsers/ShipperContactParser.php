<?php


namespace App\Services\Parsers\Drivers\WholesaleExpress\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{

    private const PATTERN_FULL_NAME = "/^(?<full_name>.+) +3KRQH\\x1D.+$/im";
    private const PATTERN_ADDRESS = "/^(?<address>.+) +\)D\[\\x1D.+$/im";
    private const PATTERN_LOCATION = "/^(?<city>[^\,$\n]+),*\s*(?<state>[a-z]{2})\s*(?<zip>[0-9]+) +\(PDLO\\x1D.+$/im";
    private const PATTERN_PHONE = "/3KRQH\\x1D (?<phone>.+)$/im";
    private const PATTERN_FAX = "/\)D\[\\x1D (?<fax>.+)$/im";
    private const PATTERN_EMAIL = "/\(PDLO\\x1D (?<email>.+)$/im";

    private string $text;

    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null,
        'fax' => null,
        'email' => null
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
            ->setEmail()
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

    private function setEmail(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_EMAIL, $this->text, $match)) {
            return $this;
        }

        $this->result['email'] = trim($match['email']);
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
