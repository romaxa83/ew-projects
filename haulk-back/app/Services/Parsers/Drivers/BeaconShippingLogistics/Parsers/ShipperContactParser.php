<?php


namespace App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{

    private const PATTERN_COMPANY_NAME = "/^(?<company_name>[^\n]+)\n/is";
    private const PATTERN_LOCATION = "/^[^\n]+\n(?<address>[^,]+), *(?<city>[^,]+), *(?<state>[a-z]{2}) *(?<zip>[0-9]+)(\s|-)/is";
    private const PATTERN_CONTACTS = "/^(?<contact_type>.+?) {3,}(?<contact_name>[0-9a-z\'\-\.\, ]+[a-z]{2,}[0-9a-z\'\-\.\, ]+)? {3,}(?<phones>\(?[0-9]{3}\)?(?: |-)?[0-9]{3}-[0-9]{4}) *(?:Ext\.? *(?<ext>[0-9]+))? *(?<email>[^\s]+\@[^\s]+\.[^\s]+)?$/im";

    private string $text;

    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null,
    ];

    public function parse(string $text): Collection
    {
        $this->text = $this->replaceBefore($text);

        return $this
            ->setFullName()
            ->setLocation()
            ->setPhones()
            ->getResult();
    }

    private function setFullName(): ShipperContactParser
    {
        preg_match(self::PATTERN_COMPANY_NAME, $this->text, $match);

        if (!empty($match['company_name'])) {
            $this->result['full_name'] = trim($match['company_name'], " \t\n\r\0\x0B,.!");
        }

        return $this;
    }

    private function setLocation(): ShipperContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text, ['address', 'city', 'state', 'zip'])
        );

        return $this;
    }

    private function setPhones(): ShipperContactParser
    {
        preg_match_all(self::PATTERN_CONTACTS, $this->text, $match);

        if (empty($match['phones'])) {
            return $this;
        }

        $phones = [];

        for ($i = 0, $max = count($match['phones']); $i < $max; $i++) {
            $phone = preg_replace("/[^0-9]+/", "", $match['phones'][$i]);
            $ext = !empty($match['ext'][$i]) ? $match['ext'][$i] : '';
            $name = trim($match['contact_name'][$i], " \t\n\r\0\x0B,.!");
            $note = trim($match['contact_type'][$i], " \t\n\r\0\x0B,.!");

            if (in_array($phone . $ext, $phones)) {
                continue;
            }

            $phones[] = $phone . $ext;

            $this->result['phones'][] = [
                'name' => !empty($name) ? $name : null,
                'number' => $phone,
                'notes' => !empty($note) ? $note : null,
                'extension' => !empty($ext) ? $ext : null
            ];
        }

        if (!empty($match['email'][0])) {
            $this->result['email'] = $match['email'][0];
        }

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
