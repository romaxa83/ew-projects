<?php

namespace App\Services\Parsers\Drivers\ShipCars\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ShipperContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^(?<address>.+),\s+(?<city>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/m";

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
        $this->text = trim($this->replacementIntend($this->replaceBefore($text)));

        return $this
            ->setFullName()
            ->setLocation()
            ->setEmail()
            ->setPhones()
            ->getResult();
    }

    private function setFullName(): self
    {
        $fullName = trim(preg_replace("/^([^\n]+)\n.+/s", "$1", $this->text));
        if (empty($fullName)) {
            return $this;
        }
        $this->result['full_name'] = $fullName;
        return $this;
    }

    private function setLocation(): self
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text, ['address', 'city', 'state', 'zip'])
        );
        return $this;
    }

    private function setEmail(): self
    {
        $email = trim(
            preg_replace(
                "/^[^\u0000-\u007F]+ +/",
                "",
                preg_replace(
                    "/.+\n([^\n]+)$/s",
                    "$1",
                    $this->text)
            )
        );
        if (empty($email)) {
            return $this;
        }
        $this->result['email'] = Str::lower($email);
        return $this;
    }

    private function setPhones(): self
    {
        $phones = trim(
            preg_replace(
                "/^[^\u0000-\u007F]+ +/",
                "",
                preg_replace(
                    "/.+\n([^\n]+)\n[^\n]+$/s",
                    "$1",
                    $this->text
                )
            )
        );
        if (empty($phones)) {
            return $this;
        }
        $phones = explode(";", $phones);
        foreach ($phones as $phone) {
            $phone = preg_replace("/\D/", "", $phone);
            if (empty($phone)) {
                continue;
            }
            $this->result['phones'][] = [
                'number' => $phone
            ];
        }
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
