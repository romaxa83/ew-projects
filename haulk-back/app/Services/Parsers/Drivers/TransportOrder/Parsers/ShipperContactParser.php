<?php


namespace App\Services\Parsers\Drivers\TransportOrder\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/(?<city>[^,]+) *\, +(?<state>[A-Z]{2}) +(?<zip>\d{3,})?/";
    private const PATTERN_PHONE = "/^Ph *\d:(?<phone>.+)$/im";
    private const PATTERN_FAX = "/^Fax:(?<fax>.+)$/im";

    private Collection $description;

    private array $result = [
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phones' => null,
        'fax' => null
    ];

    public function parse(string $text): Collection
    {
        return $this->setUp($text)
            ->setFullName()
            ->setFax()
            ->setPhone()
            ->setLocation()
            ->setAddress()
            ->getResult();
    }

    private function setUp(string $text): ShipperContactParser
    {
        $this->description = collect(
            explode(
                "\n",
                $this->replacementIntend($this->replaceBefore($text))
            )
        );

        return $this;
    }

    private function setFullName(): ShipperContactParser
    {
        $this->result['full_name'] = trim($this->description[0]);

        $this->description = $this->description->slice(1);

        return $this;
    }

    private function setFax(): ShipperContactParser
    {
        $fax = $this->description->last();

        if (!preg_match(self::PATTERN_FAX, $fax, $match)) {
            return $this;
        }

        $this->description = $this->description->slice(0, -1);

        $this->result['fax'] = preg_replace("/\D+/", "", $match['fax']);

        return $this;
    }

    private function setPhone(): ShipperContactParser
    {
        $description = $this->description;

        for ($max = $description->count(), $i = $max; $i >=0; $i--) {
            if (!preg_match(self::PATTERN_PHONE, $description[$i], $match)) {
                break;
            }
            $this->result['phones'][] = [
                'number' => preg_replace("/\D+/", "", $match['phone'])
            ];

            $this->description = $this->description->slice(0, -1);

        }
        return $this;
    }

    private function setLocation(): ShipperContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->description->last())
        );

        $this->description = $this->description->slice(0, -1);

        return $this;
    }

    private function setAddress(): ShipperContactParser
    {
        $this->result['address'] = trim($this->description->implode(" "), " \t\n\r\0\x0B.,");

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
