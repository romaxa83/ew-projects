<?php


namespace App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{

    private const PATTERN_FULL_NAME = "/^Name: +(?<full_name>.+)$/m";
    private const PATTERN_ADDRESS = "/^Address: +(?<address>.+)$/m";
    private const PATTERN_LOCATION = "/\n(?<city>[^\n]+) +(?<state>[a-z]{2}) +(?<zip>[0-9]+)\s*/is";
    private const PATTERN_PHONE = "/\nPhone:(?<phone>.+)/is";

    private string $text;

    private array $result = [
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phones' => null
    ];

    public function parse(string $text): Collection
    {
        $this->text = $this->replacementIntend($this->replaceBefore($text));

        return $this->setFullName()
            ->setAddress()
            ->setLocation()
            ->setPhone()
            ->getResult();
    }

    private function setFullName(): PickupDeliveryContactParser
    {
        if (!preg_match(self::PATTERN_FULL_NAME, $this->text, $match)) {
            return $this;
        }

        $fullName = trim($match['full_name'], " \t\n\r\0\x0B,.!");

        if (empty($fullName)) {
            return $this;
        }

        $this->result['full_name'] = $fullName;

        return $this;
    }

    private function setAddress(): PickupDeliveryContactParser
    {
        if (!preg_match(self::PATTERN_ADDRESS, $this->text, $match)) {
            return $this;
        }

        $address = trim($match['address'], " \t\n\r\0\x0B,.!");

        if (empty($address)) {
            return $this;
        }

        $this->result['address'] = $address;

        return $this;
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text)
        );
        return $this;
    }

    private function setPhone(): PickupDeliveryContactParser
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

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
