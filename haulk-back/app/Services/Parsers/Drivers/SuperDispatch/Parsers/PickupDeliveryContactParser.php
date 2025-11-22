<?php

namespace App\Services\Parsers\Drivers\SuperDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^(?<city>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/";

    private string $text;

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
        $this->text = trim($this->replacementIntend($this->replaceBefore($text)));

        return $this
            ->setPhones()
            ->setLocation()
            ->setAddress()
            ->setFullName()
            ->getCollection();
    }

    private function setPhones(): self
    {
        $phones = [];
        preg_match("/Mobile:(?<mobile>.+)$/m", $this->text, $match);
        if (!empty($match['mobile'])) {
            $mobile = preg_replace("/\D/", "", $match['mobile']);
            if (!empty($mobile)) {
                $phones[] = $mobile;
            }
        }
        if (preg_match("/Phone:(?<phone>.+)$/m", $this->text, $match)) {
            $phones = array_merge(
                $phones,
                array_map(
                    static fn(string $phone) => preg_replace("/\D/", "", $phone),
                    explode(",", $match['phone'])
                )
            );
        }
        $this->text = preg_replace("/\s*Phone:.*/s", "", $this->text);
        if (empty($phones)) {
            return $this;
        }
        $this->result['phones'] = array_map(
            static fn (string $phone): array => ['number' => $phone],
            array_values(array_unique(array_filter($phones)))
        );
        return $this;
    }

    private function setLocation(): self
    {
        $location = trim(preg_replace("/.+\n([^\n]+)$/s", "$1", $this->text), " \t\n\r\0\x0B*");
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, trim($location))
        );
        $this->text = preg_replace("/(.+)\n[^\n]+$/s", "$1", $this->text);
        return $this;
    }

    private function setAddress(): self
    {
        $address = trim(preg_replace("/.+\n([^\n]+)$/s", "$1", $this->text), " \t\n\r\0\x0B*");
        $this->result['address'] = trim($address);
        $this->text = preg_replace("/(.+)\n[^\n]+$/s", "$1", $this->text);
        return $this;
    }

    private function setFullName(): self
    {
        $names = [];
        preg_match("/^Name:(?<name>.+)$/m", $this->text, $match);
        if (!empty($match['name'])) {
            $names[] = trim($match['name'], " \t\n\r\0\x0B*.");
        }

        $this->text = trim(
            preg_replace(
                "/ {2,}/",
                " ",
                preg_replace(
                    "/\n/",
                    " ",
                    preg_replace(
                        "/Name:[^\n]*\n/s",
                        "",
                        $this->text
                    )
                )
            ),
            " \t\n\r\0\x0B*"
        );
        if (!empty($this->text)) {
            $names[] = $this->text;
        }
        $this->result['full_name'] = implode(". ", $names);

        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
