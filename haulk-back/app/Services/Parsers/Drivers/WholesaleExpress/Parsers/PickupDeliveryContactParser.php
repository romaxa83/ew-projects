<?php


namespace App\Services\Parsers\Drivers\WholesaleExpress\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{

    private const PATTERNS_CLEAR = [
        ["/\s*38\\x1D.+$/m", ""],
        ["/\s*'?\(\/\\x1D.+$/m", ""],
        ["/\n{2,}/", "\n"],
        ["/\s*$/m",""]
    ];

    private const PATTERN_LOCATION = "/^(?<city>[^,]+),+\s*(?<state>[a-z]{2})\s+(?<zip>[0-9]+)/is";
    private const PATTERN_PHONE = "/^.*(?<phone>\s*\([0-9]{3}\).+)/is";

    private string $text;

    private array $description = [];

    private bool $isDelivery = false;

    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null
    ];

    public function parse(string $text): Collection
    {
        $this->isDelivery = $this->parsingAttribute->name === 'delivery_contact';

        $this->text = trim($this->replaceBefore($text));
        return $this->clearContact()
            ->setAddress()
            ->setLocation()
            ->setPhones()
            ->setFullName()
            ->getResult();
    }

    private function clearContact(): PickupDeliveryContactParser
    {
        $intend = mb_strlen(preg_replace("/^(.+38\\x1D [\d]{2}\/[\d]{2}\/[\d]{4} \d+:\d+).+/is","$1", $this->text))+2;

        if (!$this->isDelivery) {
            $this->text = trim((string)preg_replace("/^(.{" . $intend. "}).*$/m", "$1", $this->text));
        } else {
            $this->text = trim((string)preg_replace("/^.{0," . $intend. "}(.*)$/m", "$1", $this->text));
        }

        foreach (self::PATTERNS_CLEAR as $patterns) {
            $this->text = (string)preg_replace($patterns[0], $patterns[1], $this->text);
        }

        $this->description = explode("\n", $this->text);

        return $this;
    }

    private function setAddress(): PickupDeliveryContactParser
    {
        $this->result['address'] = trim($this->description[1]);
        return $this;
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->description[2])
        );
        return $this;
    }

    private function setPhones(): PickupDeliveryContactParser
    {
        if (empty($this->description[3])) {
            return $this;
        }
        preg_match(self::PATTERN_PHONE, $this->description[3], $match);

        if (!empty($match['phone'])) {
            $this->result['phones'][] = [
                'number' => preg_replace("/[^0-9]+/", "", $match['phone'])
            ];

            $this->description[3] = str_replace($match['phone'], "", $this->description[3]);
        }

        $this->description[3] = trim($this->description[3], " \t\n\r\0\x0B.,!");

        return $this;
    }

    private function setFullName(): PickupDeliveryContactParser
    {
        $companyName = trim($this->description[0], " \t\n\r\0\x0B.,!");

        if (!empty($companyName)) {
            $this->result['full_name'] = $companyName;
        }
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
