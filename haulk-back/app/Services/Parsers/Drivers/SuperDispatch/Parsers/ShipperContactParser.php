<?php

namespace App\Services\Parsers\Drivers\SuperDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ShipperContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^(?<city>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/";

    private string $text;

    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null,
        'email' => null
    ];

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text);

        return $this
            ->setEmail()
            ->setPhones()
            ->setLocation()
            ->setAddress()
            ->setFullName()
            ->getResult();
    }

    private function setEmail(): self
    {
        preg_match("/^Email:\s*(?<email>\S+)$/m", $this->text, $match);
        if (empty($match['email'])) {
            return $this;
        }
        $this->result['email'] = Str::lower($match['email']);
        return $this;
    }

    private function setPhones(): self
    {
        preg_match("/^Co\. Phone:\s*(?<phone>.+)$/m", $this->text, $match);
        if (empty($match['phone'])) {
            return $this;
        }
        $phone = preg_replace(
            "/\D/",
            "",
            preg_replace(
                "/ext.+/i",
                "",
                $match['phone']
            )
        );
        if (empty($phone)) {
            return $this;
        }
        $this->result['phones'][] = [
            'number' => $phone
        ];
        return $this;
    }

    private function setLocation(): self
    {
        $this->text = preg_replace("/\s*Co\. Phone.*/s", "", $this->text);
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
        $name = trim(
            preg_replace(
                "/ {2,}/",
                " ",
                preg_replace(
                    "/\n/",
                    " ",
                    $this->text
                )
            ),
            " \t\n\r\0\x0B*"
        );
        if (empty($name)) {
            return $this;
        }
        $this->result['full_name'] = $name;
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
