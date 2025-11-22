<?php


namespace App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{

    private const PATTERN_LOCATION = "/^(?<address>[^\n]+)\n(?<city>[^,]+), *(?<state>[a-z]{2}) *(?<zip>[0-9]+)(\s|-)/is";
    private const PATTERN_PHONE = "/^(?<phone>[\d\-\(\) ]+)$/m";
    private const PATTERN_FAX = "/or Fax:\s*(?<fax>.+)$/m";
    private const PATTERN_EMAIL = "/to:\s*(?<email>.+)\s*or Fax:\s/m";

    private string $text;

    private array $result = [
        'full_name' => 'Direct Connect Logistix',
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
            ->setLocation()
            ->setPhone()
            ->setFax()
            ->setEmail()
            ->getResult();
    }

    private function setLocation(): ShipperContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text, ['address', 'city', 'state', 'zip'])
        );

        return $this;
    }

    private function setPhone(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_PHONE, $this->text, $match)) {
            return $this;
        }

        $phone = preg_replace("/[^0-9]+/", "", $match['phone']);

        if (empty($phone)) {
            return $this;
        }

        $this->result['phones'][] = [
            'number' => $phone
        ];

        return $this;
    }

    private function setFax(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_FAX, $this->text, $match)) {
            return $this;
        }

        $fax = preg_replace("/[^0-9]+/", "", $match['fax']);

        if (empty($fax)) {
            return $this;
        }

        $this->result['fax'] = $fax;

        return $this;
    }

    private function setEmail(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_EMAIL, $this->text, $match)) {
            return $this;
        }

        $email = trim($match['email']);

        if (empty($email)) {
            return $this;
        }

        $this->result['email'] = $email;

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
