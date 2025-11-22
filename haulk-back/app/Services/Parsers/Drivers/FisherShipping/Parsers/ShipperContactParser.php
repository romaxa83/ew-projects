<?php


namespace App\Services\Parsers\Drivers\FisherShipping\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{

    private const PATTERN_CHECK_IS_SET_CONTACT = "/Transportation Terms & Conditions/";

    private const PATTERN_LOCATION = "/^(?<address>.+), *(?<city>[^,]+), *(?<state>[a-z]{2}) *(?<zip>[0-9]+)($|-)/im";
    private const PATTERN_PHONE = "/^Phone *# *(?<phone>[\d\-\(\) ]+)$/m";
    private const PATTERN_EMAIL = "/SEND INVOICES TO:\s*(?<email>.+)$/m";

    private string $text;

    private array $result = [
        'full_name' => 'Fisher Shipping',
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null,
        'email' => null
    ];

    public function parse(string $text): Collection
    {
        if (!$this->isSetContactData($text)) {
            return $this->getResult();
        }

        $this->text = $this->replaceBefore($text);

        return $this->setLocation()
            ->setPhone()
            ->setEmail()
            ->getResult();
    }

    private function isSetContactData(string $text): bool
    {
        return (bool)preg_match(self::PATTERN_CHECK_IS_SET_CONTACT, $text);
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

    private function setEmail(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_EMAIL, $this->text, $match)) {
            return $this;
        }

        $email = trim($match['email']);

        if (empty($email)) {
            return $this;
        }

        $this->result['email'] = mb_strtolower($email);

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
