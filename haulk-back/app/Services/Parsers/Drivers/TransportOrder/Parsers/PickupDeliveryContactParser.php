<?php


namespace App\Services\Parsers\Drivers\TransportOrder\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
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
        $this->description = collect(
            explode(
                "\n",
                $this->replacementIntend($this->replaceBefore($text))
            )
        );

        return $this
            ->setFax()
            ->setPhone()
            ->setLocation()
            ->setAddress()
            ->setFullName()
            ->getCollection();
    }

    private function setFullName(): PickupDeliveryContactParser
    {
        $this->result['full_name'] = trim($this->description->implode(" "), " \t\n\r\0\x0B,.!");

        return $this;
    }

    private function setAddress(): PickupDeliveryContactParser
    {
        $this->result['address'] = trim($this->description->last(), " \t\n\r\0\x0B,.");

        $this->description = $this->description->slice(0, -1);
        return $this;
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, trim($this->description->last()))
        );

        $this->description = $this->description->slice(0, -1);

        return $this;
    }

    private function setPhone(): PickupDeliveryContactParser
    {
        $description = $this->description;

        for ($max = count($description), $i = $max-1; $i >= 0; $i--) {
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

    private function setFax(): PickupDeliveryContactParser
    {
        $fax = $this->description->last();

        if (!preg_match(self::PATTERN_FAX, $fax, $match)) {
            return $this;
        }

        $this->result['fax'] = preg_replace("/\D+/", "", $match['fax']);

        $this->description = $this->description->slice(0, -1);

        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
