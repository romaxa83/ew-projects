<?php


namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{

    private const PATTEN_INTEND = "/(?<intend>.+)Dispatch Info/i";
    private const PATTERN_CONTACT_NAME = "/Dispatch Info\s*Contact: (?<contact_name>.+)\nPhone/is";
    private const PATTERN_PHONES = "/(?:Phone|Cell)(?: [0-9]+)?: +(?<phone>[^a-z]+[0-9](?:\s|$))/is";
    private const PATTERN_FAX = "/Fax: +(?<fax>[^a-z\n]+)/is";
    private const PATTERN_CLEAR_PARSED_INFO = "/\n[^\n]*Phone:.*/is";
    private const PATTERN_LOCATION = "/\n(?<address>(?:[0-9]+|PO) .+)\n(?<city>[^\n\,]+?), *(?<state>[a-z]{2}) *?(?<zip>[0-9]+)$/is";
    private const PATTERN_CLEAR_PARSED_LOCATION_INFO = "/\n(?<address>(?:[0-9]+|PO) .+)\n(?<city>[^\n\,]+?), *(?<state>[a-z]{2}) *?(?<zip>[0-9]+)$/is";

    private string $text;

    private array $result = [
        'contact_name' => null,
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
        $this->text = trim($this->replaceBefore($text));
        return $this
            ->clearText()
            ->setContactName()
            ->setPhone()
            ->setFax()
            ->removeParsedInfo()
            ->setLocation()
            ->removeParsedLocationInfo()
            ->setFullName()
            ->getCollection();
    }

    private function clearText(): ShipperContactParser
    {
        preg_match(self::PATTEN_INTEND, $this->text, $match);
        $intend = mb_strlen($match['intend']);

        $this->text = trim(preg_replace("/^.{0," . $intend ."}/m", "", $this->text));

        $this->text = (string)preg_replace("/\n{3,}\s*Total.*/s", "", $this->text);
        $this->text = (string)preg_replace("/\n{2,}/", "\n", $this->text);

        return $this;
    }

    private function setContactName(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_CONTACT_NAME, $this->text, $match)) {
            return $this;
        }
        $this->result['contact_name'] = trim(preg_replace("/\n/s", " ", $match['contact_name']));
        return $this;
    }

    private function setPhone(): ShipperContactParser
    {
        preg_match_all(self::PATTERN_PHONES, $this->text, $match);

        if (empty($match['phone'])) {
            return $this;
        }

        $phones = [];

        foreach ($match['phone'] as $phone) {
            $phone = preg_replace("/[^0-9]+/", "", $phone);
            if (empty($phone) || in_array($phone, $phones)) {
                continue;
            }
            $phones[] = $phone;

            $this->result['phones'][] = [
                'number' => $phone
            ];
        }
        return $this;
    }

    private function setFax(): ShipperContactParser
    {
        if (!preg_match(self::PATTERN_FAX, $this->text, $match)) {
            return $this;
        }
        $fax = preg_replace("/[^0-9]+/", "", $match['fax']);

        if (empty($fax)) {
            return $this;
        }
        $this->result['fax'] = $fax;
        return $this;
    }

    private function removeParsedInfo(): ShipperContactParser
    {
        $this->text = (string)preg_replace(self::PATTERN_CLEAR_PARSED_INFO, "", $this->text);
        return $this;
    }

    private function setLocation(): ShipperContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text, ['address', 'state', 'city', 'zip'])
        );

        return $this;
    }

    private function removeParsedLocationInfo(): ShipperContactParser
    {
        $this->text = (string)preg_replace(self::PATTERN_CLEAR_PARSED_LOCATION_INFO, "", $this->text);
        return $this;
    }

    private function setFullName(): ShipperContactParser
    {
//        $lines = explode("\n", $this->text);
//        $lines = preg_grep('/^\s.*$/', $lines, PREG_GREP_INVERT);
//        $result = implode("\n", $lines);

        $parts = preg_split('/\d/', $this->text, 2);

//        dd($this->text, trim(preg_replace("/\n/s", " ", $this->text)), $result);
        $this->result['full_name'] = trim(preg_replace("/\n/s", " ", $parts[0]));

        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
