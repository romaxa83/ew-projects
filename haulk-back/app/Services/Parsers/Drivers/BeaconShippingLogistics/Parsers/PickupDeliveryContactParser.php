<?php


namespace App\Services\Parsers\Drivers\BeaconShippingLogistics\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{

    private const PATTERN_CLEAR_DATE = "/\s*Address:\s*.+/s";
    private const PATTERN_DATE = "/(?<week_day>[a-z]+), *(?<month>[a-z]+) +(?<day>[0-9]{1,2}), +(?<year>[0-9]{4})$/im";

    private const PATTERNS_CLEAR = [
        ["/.+(Address: +.+)/is", "$1"],
        ["/\s*Hours:.+Buyer.+/is", ""]
    ];
    private const PATTERN_LOCATION = "/^Address: +(?<address>.+?) {3,}(?<city>.+?) {3,}(?<state>[a-z]{2}) {3,}(?<zip>[0-9]+)(\s|-)/is";
    private const PATTERN_PHONES = "/(?<phones>\(?[0-9]{3}\)?(?: |-)?[0-9]{3}-[0-9]{4})/is";

    private const PATTERN_FULL_NAME = "/^Contact:(?<contact_name>[^\n]+)\n\s*Location:(?<company_name>.*)/is";

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
            ->setFullName()
            ->getResult();
    }

    private function clearContact(): PickupDeliveryContactParser
    {
        $this->setDate();

        foreach (self::PATTERNS_CLEAR as $patterns) {
            $this->text = (string)preg_replace($patterns[0], $patterns[1], $this->text);
        }

        return $this;
    }

    private function setDate(): void
    {
        $text = preg_replace(self::PATTERN_CLEAR_DATE, "", $this->text);

        preg_match(self::PATTERN_DATE, $text, $match);

        $this->result[$this->isDelivery ? 'delivery_date' : 'pickup_date'] = Carbon::createFromFormat("Y F d", $match['year'] . ' ' . $match['month'] . ' ' . $match['day'])->format('m/d/Y');
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text, ['address', 'city', 'state', 'zip'])
        );

        $this->text = trim(preg_replace("/^[^\n]+\n/s", "", $this->text));

        return $this;
    }

    private function setPhones(): PickupDeliveryContactParser
    {
        preg_match_all(self::PATTERN_PHONES, $this->text, $match);

        if (empty($match['phones'])) {
            return $this;
        }

        $phones = [];

        foreach ($match['phones'] as $phone) {
            $phone = preg_replace("/[^0-9]+/", "", $phone);

            if (in_array($phone, $phones)) {
                continue;
            }

            $phones[] = $phone;

            $this->result['phones'][] = [
                'number' => $phone
            ];
        }

        $this->text = (string)preg_replace(self::PATTERN_PHONES, "", $this->text);

        return $this;
    }

    private function setFullName(): PickupDeliveryContactParser
    {
        preg_match(self::PATTERN_FULL_NAME, $this->text, $match);

        if (!empty($match['company_name'])) {
            $this->result['full_name'] = trim($match['company_name'], " \t\n\r\0\x0B,.!");
        }

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
