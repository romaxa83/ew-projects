<?php

namespace App\Services\Parsers\Drivers\WindyTransLogistics\Parsers;

use App\Services\Parsers\Objects\ParserPartIntend;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{
    private const LOCATION_PATTERN = "/^(?<city>[^,]+), (?<state>[A-Z]{2}) (?<zip>[0-9]+)$/";
    private const PHONE_PATTERN = "/\(?[0-9]{3}[)\- ]?[0-9]{3}[\- ]?[0-9]{4}/";

    private string $text;
    private int $intend;

    private array $result = [
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phones' => null,
        'fax' => null,
        'instruction' => null
    ];

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text, false);

        return $this
            ->removeVehicleData()
            ->calculateIntend()
            ->refactorText()
            ->splitContactAndInstruction($this->parsingAttribute->name === 'pickup_contact')
            ->getResult();
    }

    private function removeVehicleData(): self
    {
        $vehicle = ValueParserAbstract::replaceByReplacement(
            $this->text,
            [
                ["/.+\nDestination:[^\n]+\n/s", "\n"],
                ["/Freight:/s", ""],
                ["/\nContracted.+/s", ""],
                ["/^ +/m", ""],
                ["/^\n/m", ""],
                ["/^([0-9]{4} [^ ]+ [^ ]+).*/s", "$1"]
            ]
        );
        $this->text = preg_replace("/\s*" . preg_quote($vehicle) . ".+/is", "", $this->text);
        return $this;
    }

    private function calculateIntend(): self
    {
        preg_match("/^(?<intend>\s*Origin {3,})Destination/m", $this->text, $match);
        $this->intend = strlen($match['intend']);
        return $this;
    }

    private function refactorText(): self
    {
        $this->text = ParserPartIntend::refactorText($this->intend, $this->text);
        return $this;
    }

    private function splitContactAndInstruction(bool $pickup = true): self
    {
        $text = ParserPartIntend::removeIntent(
            $this->text,
            $this->intend,
            $pickup ? ParserPartIntend::TYPE_RIGHT : ParserPartIntend::TYPE_LEFT
        );
        $text = ValueParserAbstract::replaceByReplacement(
            $text,
            [
                ["/^[^\n]+\n/s", ""],
                ["/ +$/m", ""],
                ["/^ +/m", ""],
                ["/\n{2,}/s", "\n"],
                ["/\(\s*hrs[^\)]*\)/", ""]
            ]
        );
        $text = explode("\n", trim($text));
        $locationKey = null;
        $contact = [];
        $instructions = [];
        foreach ($text as $key => $item) {
            if ($locationKey && !preg_match(self::PHONE_PATTERN, $item)) {
                $startInstruction = true;
                $instructions[] = $item;
                continue;
            }
            if ($locationKey && !empty($startInstruction)) {
                $instructions[] = $item;
                continue;
            }
            $contact[] = $item;
            if (!preg_match(self::LOCATION_PATTERN, $item)) {
                continue;
            }
            $locationKey = $key;
        }
        if ($locationKey === null) {
            return $this;
        }
        $this->result['instruction'] = $this->clearText(implode("\n", $instructions));
        $this->result['address'] = $contact[$locationKey - 1];
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::LOCATION_PATTERN, $contact[$locationKey])
        );
        $this->result['full_name'] = trim($contact[$locationKey - 2]);
        $_phones = array_slice($contact, $locationKey + 1);
        if (empty($_phones)) {
            return $this;
        }
        $phones = [];
        foreach ($_phones as $phone) {
            if (!preg_match("/fax/i", $phone)) {
                $phone = preg_split("/(,|;| and )/", $phone);
                foreach ($phone as $item) {
                    $phones[] = preg_replace("/\D/", "", $item);
                }
                continue;
            }
            $this->result['fax'] = preg_replace("/\D/", "", $phone);
        }
        $this->result['phones'] = array_map(
            static fn(string $phone): array => ['number' => $phone],
            array_values(
                array_unique($phones)
            )
        );
        return $this;
    }

    private function clearText(string $text): ?string
    {
        $text = trim(
            ValueParserAbstract::replaceByReplacement(
                $text,
                [
                    ["/\n/", " "],
                    ["/ {2,}/", " "]
                ]
            )
        );
        return !empty($text) ? $text : null;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
