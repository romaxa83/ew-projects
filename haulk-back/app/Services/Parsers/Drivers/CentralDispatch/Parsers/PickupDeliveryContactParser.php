<?php


namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{

    private const PATTERN_INTEND = "/(?<intend>.+)Delivery Information/i";
    private const PATTERNS_FOR_CLEAR_TEXT = [
        ["/.+(Name:)/is", "$1"],
        ["/\s*$/m", "\n"],
        ["/^\s*/m", ""],
        ["/(^[^\n]*?\([^\n]+?)\n([^\n]+\)\n)/s", "$1 $2"],
        ["/(^Name:[^\(\n]+)\n([^\(\n]*\(.+\)\n)/s", "$1 $2"],
        ["/\n[^\n]*Buyer Num:[^\n]*\n/is", "\n"],
        ["/(?<line>\n[^\n]+)(\k<line>(?:\n|, | ))/is", "$1\n"]
    ];

    private const PATTERN_LOCATION = "/(?<city>[a-z]* *[a-z]* *[a-z]+) *\,\s+(?<state>[A-Z]{2})(?:\s+(?<zip>[0-9]{3,}))?$/";
    private const PATTERN_ADDRESS = "/^([a-z]*[0-9]+[a-z0-9]* [0-9a-z]+|PO|\*CONTACT).+/i";
    private const PATTERN_PHONES = "/(?<type>[a-z]+)(?: [0-9]+)?: +(?<phone>[^a-z]+)/i";

    private const LOCATION_LINE = 2;

    private string $text;

    private array $description = [];

    private bool $isDelivery = true;

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
        $this->text = trim($this->replaceBefore($text));

        $this->isDelivery = $this->parsingAttribute->name === 'delivery_contact';

        return $this->clearInfo()
            ->setFullName()
            ->setAddress()
            ->setLocation()
            ->removeParsedInfo()
            ->setPhones()
            ->getCollection();
    }

    private function clearInfo(): PickupDeliveryContactParser
    {
        preg_match(self::PATTERN_INTEND, $this->text, $match);

        $intend = mb_strlen($match['intend']);
        $text = $this->text;

        if ($this->isDelivery) {
            $text = preg_replace("/^.{0," . $intend . "}/m", "", $text);
        } else {
            $text = preg_replace("/^(.{" . ($intend-1) . "}).*$/m", "$1", $text);
        }

        foreach (self::PATTERNS_FOR_CLEAR_TEXT as $pattern) {
            $text = preg_replace($pattern[0], $pattern[1], $text);
        }

        $this->description = explode("\n", trim($text));

        return $this;
    }

    private function setFullName(): PickupDeliveryContactParser
    {
        $companyName = $this->getCompanyName();
        $fullName = null;

        if ($companyName) {
            $this->description[0] = trim(str_replace($companyName, "", $this->description[0]));
            $companyName = trim($companyName, " \t\n\r\0\x0B,.()");

            if (!empty($companyName)) {
                $this->result['full_name'] = $companyName;
            }
        }

        return $this;
    }

    private function getCompanyName(): ?string
    {
        //Method for central_dispatch_44.pdf
        $text = trim($this->description[0]);
        $length = strlen($text);

        if ($text[$length-1] !== ')') {
            return null;
        }

        $close = 0;

        for ($i = $length-1; $i > 0; $i--) {
            if ($text[$i] === ')') {
                $close++;
            }
            if ($text[$i] === '(') {
                $close--;
            }
            if ($close === 0) {
                break;
            }
        }

        return mb_substr($text, $i-1);
    }

    private function setAddress(): PickupDeliveryContactParser
    {
        if (preg_match(self::PATTERN_ADDRESS, $this->description[1])) {
            $this->result['address'] = trim($this->description[1], " \t\n\r\0\x0B\,");
            return $this;
        }
        //For example pickup contact in central_dispatch_24.pdf and central_dispatch_42.pdf
        if (preg_match(self::PATTERN_ADDRESS, $this->description[2])) {
            $this->result['address'] = trim($this->description[2], " \t\n\r\0\x0B\,");

            unset($this->description[1]);
            $this->description = array_values($this->description);
            return $this;
        }
        return $this;
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        for ($i = 0, $max = count($this->description); $i < $max; $i++) {
            $this->result = array_merge(
                $this->result,
                $this->parseLocation(self::PATTERN_LOCATION, trim($this->description[$i]))
            );
            if (!empty($this->result['state'])) {
                //For pickup contact in central_dispatch_24.pdf
                if ($this->description[$i] === $this->result['address']) {
                    return $this->splitAddress();
                }

                if ($i !== self::LOCATION_LINE) {
                    unset($this->description[self::LOCATION_LINE]);
                    $this->description = array_values($this->description);
                }
                break;
            }
        }
        return $this;
    }

    private function splitAddress(): PickupDeliveryContactParser
    {
        if (!preg_match("/[a-z]* *[a-z]* *[a-z]+, +[A-Z]{2} +[0-9]{3,}$/", $this->result['address'], $match)) {
            return $this;
        }

        $this->result['address'] = trim(str_replace($match[0], "", $this->result['address']));
        $this->description = array_merge(
            [
                $this->description[0],
                $this->result['address'],
                trim($match[0])
            ],
            array_slice($this->description, 2)
        );

        return $this->setLocation();
    }

    private function removeParsedInfo(): PickupDeliveryContactParser
    {
        unset($this->description[0], $this->description[1], $this->description[2]);
        $this->text = implode(" ", $this->description);
        return $this;
    }

    private function setPhones(): PickupDeliveryContactParser
    {
        preg_match_all(self::PATTERN_PHONES, $this->text, $match);

        $phones = [];

        foreach ($match['phone'] as $key => $phone) {

            $phone = preg_replace("/[^0-9]+/", "", $phone);

            if (empty($phone)) {
                continue;
            }

            if (!preg_match("/fax/i", $match['type'][$key])) {
                if (in_array($phone, $phones)) {
                    continue;
                }
                $phones[] = $phone;
                $this->result['phones'][] = [
                    'number' => $phone
                ];
                continue;
            }

            $this->result['fax'] = $phone;
        }

        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
