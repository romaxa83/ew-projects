<?php


namespace App\Services\Parsers\Drivers\AcvTransportation\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{

    private const PATTERN_INTEND = "/^(?<intend>.+)Delivery Details/m";

    private const PATTERNS_FOR_CLEAR_TEXT = [
        ["/\s*Pickup Details\s*/is", ""],
        ["/\s*Delivery Details\s*/is", ""],
        ["/^ +/m", ""],
        ["/ +$/m", ""],
        ["/\n{2,}/s", "\n"]
    ];

    private const PATTERN_LOCATION = "/(?<city>[^,]+) *\, +(?<state>[A-Z]{2}) +(?<zip>[0-9]{3,})?/";
    private const PATTERN_PHONE = "/^Phone:(?<phone>.+)$/im";

    private string $text;

    private array $description = [];

    private bool $isDelivery = true;

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

        $this->isDelivery = $this->parsingAttribute->name === 'delivery_contact';

        return $this->clearInfo()
            ->setFullName()
            ->setAddress()
            ->setLocation()
            ->setPhone()
            ->getCollection();
    }

    private function clearInfo(): PickupDeliveryContactParser
    {
        preg_match(self::PATTERN_INTEND, $this->text, $match);

        $intend = mb_strlen($match['intend']);
        $text = $this->text;

        if ($this->isDelivery) {
            $text = preg_replace("/^.{0," . $intend . "}/m", "", $text);
        } else {
            $text = preg_replace("/^(.{" . ($intend-1) . "}).*$/m", "$1", $text);
        }

        foreach (self::PATTERNS_FOR_CLEAR_TEXT as $pattern) {
            $text = preg_replace($pattern[0], $pattern[1], $text);
        }

        $this->text = trim($text);

        $this->description = explode("\n", trim($text));

        return $this;
    }

    private function setFullName(): PickupDeliveryContactParser
    {
        $this->result['full_name'] = trim($this->description[0], " \t\n\r\0\x0B,.!");

        return $this;
    }

    private function setAddress(): PickupDeliveryContactParser
    {
        $this->result['address'] = trim($this->description[1], " \t\n\r\0\x0B,.");
        return $this;
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        $tmp = explode(",", $this->description[2], 2);
        if (isset($tmp[0])) {
            $this->result['city'] = $tmp[0];
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

        return $this;
    }

    private function setPhone(): PickupDeliveryContactParser
    {
        if (!preg_match(self::PATTERN_PHONE, $this->text, $match)) {
            return $this;
        }
        $this->result['phones'][] = [
            'number' => preg_replace("/[^0-9]+/", "", $match['phone'])
        ];


        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
