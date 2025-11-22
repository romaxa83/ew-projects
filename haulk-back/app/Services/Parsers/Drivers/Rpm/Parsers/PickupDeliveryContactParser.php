<?php

namespace App\Services\Parsers\Drivers\Rpm\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^(?<address>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/";

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
        $this->text = trim($this->replacementIntend($this->replaceBefore($text, false)));

        return $this
            ->setPhones()
            ->setLocation()
            ->setFullName()
            ->getCollection();
    }

    private function setPhones(): self
    {
        $split = explode("\n", $this->text);
        if (empty($split[2])) {
            return $this;
        }
        $this->text = preg_replace("/\n[^\n]+$/", "", $this->text);
        if (preg_match("/[a-z]+/i", $split[2])) {
            return $this;
        }
        $this->result['phones'] = array_map(
            static fn (string $phone): array => ['number' => $phone],
            array_values(
                array_unique(
                    array_map(
                        static fn (string $item): string => preg_replace("/\D/", "", $item),
                        preg_split("/[;,]/", $split[2])
                    )
                )
            )
        );
        return $this;
    }

    private function setLocation(): self
    {
        $location = trim(preg_replace("/.+\n([^\n]+)$/s", "$1", $this->text));
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $location, ['address', 'state', 'zip'])
        );
        $this->text = trim(preg_replace("/(.+)\n[^\n]+$/s", "$1", $this->text));
        return $this;
    }

    private function setFullName(): self
    {
        $this->result['full_name'] = $this->text;
        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
