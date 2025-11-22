<?php


namespace App\Services\Parsers\Drivers\AcvTransportation\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{

    private const PATTERN_FULL_NAME = "/Dispatched *by *: *(?<full_name>[^\n]+)/is";
    private const PATTERN_PHONE = "/^Phone *: *(?<phone>.+)$/im";
    private const PATTERN_LOCATION = "/(?<city>[^,]+?) *, *(?<state>[a-z]{2}) *?(?<zip>[0-9]+)$/is";

    private string $text;

    private array $description = [];

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
        $this->text = trim($this->replaceBefore($text));

        return $this->splitText()
            ->setFullName()
            ->setAddress()
            ->setLocation()
            ->setPhone()
            ->getResult();
    }

    private function splitText(): ShipperContactParser
    {
        $this->description = explode("\n", $this->text);

        return $this;
    }

    private function setFullName(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_FULL_NAME, $this->text, $match)) {
            return $this;
        }

        $name = trim($match['full_name']);
        $this->result['full_name'] = $name == '-' ? null : $name;

        return $this;
    }

    private function setAddress(): ShipperContactParser
    {
        $this->result['address'] = trim($this->description[0], " \t\n\r\0\x0B.,");

        return $this;
    }

    private function setLocation(): ShipperContactParser
    {
        if(isset($this->description[1])){
            $tmp = explode(',', $this->description[1]);
            if(isset($tmp[0])){
                $this->result['city'] = trim($tmp[0]);
            }
            if(isset($tmp[1])){
                preg_match('/\d+/', $tmp[1], $matches);
                if (isset($matches[0])) {
                    $this->result['zip'] = $matches[0];
                }
                preg_match('/[a-zA-Z]+/', $tmp[1], $matches);
                if (isset($matches[0])) {
                    $this->result['state'] = $matches[0];
                }
            }
        }

        return $this;
    }

    private function setPhone(): ShipperContactParser
    {
        if (!preg_match('/Phone:(.*)Auction ID/s', $this->text, $match)) {
            return $this;
        }

        $this->result['phones'][] = [
            'number' => str_replace("-", '', trim($match[1]))
        ];

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
