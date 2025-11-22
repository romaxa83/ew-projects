<?php


namespace App\Services\Parsers\Drivers\WholesaleExpress2\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{

    private const PATTERNS_CLEAR = [
        ["/^\s*$/m", ""],
        ["/^ */m", ""],
        ["/\n{2,}/", "\n"],
        ["/^[^\n]+\n[^\n]+\n/s", ""]
    ];

    private const PATTERNS_INTEND = [
        'pickup' => "/^(?<intend>P\s*.+)Miles\s*D\s*/m",
        'delivery' => "/^(?<intend>P\s*.+Miles\s*D)\s*/m"
    ];

    private const PATTERN_DATE = "/^(?<date>[0-9]{2}\-[a-z]+\-[0-9]{4})/i";
    private const PATTERN_LOCATION = "/^(?<city>[^,]+),+\s*(?<state>[a-z]{2})[^0-9]*(?<zip>[0-9]+)(\s|-)/is";
    private const PATTERN_PHONES = "/(?<phone>^[^a-z\n]+)?\n.*?(?:\nCel:(?<add_phone>.+))?$/is";
    private const PATTERN_ADDRESS = "/\n(?<address>[^\n]+)$/is";
    private const PATTERN_CONTACT_NAME = "/^(?<contact_name>[^\n]+)\n/";

    private string $text;

    private bool $isDelivery = false;

    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null
    ];

    public function parse(string $text): Collection
    {
        $this->isDelivery = $this->parsingAttribute->name === 'delivery_contact';

        $this->text = $this->replaceBefore($text);
        return $this->clearContact()
            ->setLocation()
            ->setPhones()
            ->setAddress()
            ->setFullName()
            ->getResult();
    }

    private function clearContact(): PickupDeliveryContactParser
    {
        if (!$this->isDelivery) {
            preg_match(self::PATTERNS_INTEND['pickup'], $this->text, $match);

            $intend = mb_strlen($match['intend'])-10;

            $this->text = trim((string)preg_replace("/^(?:[a-z]\s)?(.{" . $intend. "}).*$/im", "$1", $this->text));
        } else {
            preg_match(self::PATTERNS_INTEND['delivery'], $this->text, $match);
            $intend = mb_strlen($match['intend']);

            $this->text = trim((string)preg_replace("/^.{0," . $intend. "}(.*)$/m", "$1", $this->text));
        }

        $this->setDate();

        foreach (self::PATTERNS_CLEAR as $patterns) {
            $this->text = (string)preg_replace($patterns[0], $patterns[1], $this->text);
        }

        return $this;
    }

    private function setDate(): void
    {
        if (!preg_match(self::PATTERN_DATE, trim($this->text), $match)) {
            return;
        }

        $this->result[$this->isDelivery ? 'delivery_date' : 'pickup_date'] = Carbon::parse($match['date'])->format('m/d/Y');
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text)
        );

        $this->text = (string)preg_replace("/^[^\n]+\n/s", "", $this->text);
        return $this;
    }

    private function setPhones(): PickupDeliveryContactParser
    {
        if (!preg_match(self::PATTERN_PHONES, $this->text, $match)) {
            return $this;
        }

        if (empty($match['phone']) && empty($match['add_phone'])) {
            return $this;
        }

        if (!empty($match['phone'])) {
            $phones[] = preg_replace("/[^0-9]+/", "", $match['phone']);
        }

        if (!empty($match['add_phone'])) {
            $phones[] = preg_replace("/[^0-9]+/", "", $match['add_phone']);

            $this->text = (string)preg_replace("/\s*Cel:.+/", "", $this->text);
        }

        $this->text = (string)preg_replace("/^[^\n]+\n/s", "", $this->text);

        $phones = array_unique($phones);

        foreach ($phones as $phone) {
            $this->result['phones'][] = [
                'number' => $phone
            ];
        }

        return $this;
    }

    private function setAddress(): PickupDeliveryContactParser
    {
        if (!preg_match(self::PATTERN_ADDRESS, $this->text, $match)) {
            return $this;
        }
        $this->result['address'] = trim($match['address']);

        $this->text = (string)preg_replace(self::PATTERN_ADDRESS, "", $this->text);
        return $this;
    }

    private function setFullName(): PickupDeliveryContactParser
    {
        //If exist contact name
        if (preg_match("/^[^\n]*[A-Z][a-z]+[^\n]+\n/", $this->text)) {
            $this->text = (string)preg_replace(self::PATTERN_CONTACT_NAME, "", $this->text);
        }

        $companyName = trim(preg_replace("/\n/", " ", $this->text), " \t\n\r\0\x0B.,");

        $companyName = preg_replace("/ {2,}/", " ", $companyName);

        if (!empty($companyName)) {
            $this->result['full_name'] = $companyName;
        }
        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
