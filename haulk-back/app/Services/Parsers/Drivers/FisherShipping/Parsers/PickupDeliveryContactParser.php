<?php


namespace App\Services\Parsers\Drivers\FisherShipping\Parsers;


use App\Exceptions\Parser\PdfFileException;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupDeliveryContactParser extends ValueParserAbstract
{

    private const PATTERN_CHECK_IS_SET_CONTACT = "/L *a *s *t N *a *m *e & F *i *r *s *t N *a *m *e *:.+?C *i *t *y, S *t *a *t *e & Z *i *p C *o *d *e:/s";

    private const PATTERN_FULL_NAME = "/^Location: +(?<company_name>[^\n]+)\nLast Name & First Name: +(?<last_name>[^\n]+?)? {3,}(?<first_name>[^\n]+?)?\n/is";
    private const PATTERN_ADDRESS = "/^Address: +(?<address>.+)$/m";
    private const PATTERN_LOCATION = "/C *i *t *y, S *t *a *t *e & Z *i *p C *o *d *e: +(?<city>[^\n]+)\s+(?<state>[a-z]{2})\s+(?<zip>[0-9]+)\s*/is";
    private const PATTERN_LOCATION_2 = "/Address:[^\n]+\n(?<city>[^\n]+)\s+(?<state>[a-z]{2})\nC *i *t *y, S *t *a *t *e & Z *i *p C *o *d *e:\n(?<zip>[0-9]+)\n/is";

    private const PATTERN_CLEAR_PHONES = "/.+Phone numbers:\s*/is";
    private const PATTERN_PHONES = "/(?<phone>\(?[0-9]{3}\)?(?: |-)[0-9]{3}(?: |-)[0-9]{4}) ?(?:\((?<notes>[a-z]+[^\)$]+)(?:\)|$))? ?(e?xt?(?:\.|:)? ?(?<ext>[0-9]+))?/is";

    private string $text;

    private array $result = [
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phones' => null
    ];

    public function parse(string $text): Collection
    {
        $this->checkIsNotImage($text);

        $this->text = $this->replaceBefore($text);

        return $this->setFullName()
            ->setAddress()
            ->setLocation()
            ->setPhones()
            ->getResult();
    }

    /**
     * @param string $text
     * @throws PdfFileException
     */
    private function checkIsNotImage(string $text): void
    {
        if (preg_match(self::PATTERN_CHECK_IS_SET_CONTACT, $text)) {
            return;
        }

        throw new PdfFileException();
    }


    private function setFullName(): PickupDeliveryContactParser
    {
        if (!preg_match(self::PATTERN_FULL_NAME, $this->text, $match)) {
            return $this;
        }

        if (!empty($match['company_name'])) {
            $companyName = trim($match['company_name'], " \t\n\r\0\x0B,.!?");

            if (!empty($companyName)) {
                $this->result['full_name']= $companyName;
            }
        }

        return $this;
    }

    private function setAddress(): PickupDeliveryContactParser
    {
        if (!preg_match(self::PATTERN_ADDRESS, $this->text, $match)) {
            return $this;
        }

        $address = trim($match['address'], " \t\n\r\0\x0B,.!");

        if (empty($address)) {
            return $this;
        }

        $this->result['address'] = $address;

        return $this;
    }

    private function setLocation(): PickupDeliveryContactParser
    {
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(self::PATTERN_LOCATION, $this->text)
        );

        if (empty($this->result['city'])) {
            $this->result = array_merge(
                $this->result,
                $this->parseLocation(self::PATTERN_LOCATION_2, $this->text)
            );
        }
        return $this;
    }

    private function setPhones(): PickupDeliveryContactParser
    {
        $phones = preg_replace(self::PATTERN_CLEAR_PHONES, "", $this->text);

        preg_match_all(self::PATTERN_PHONES, $phones, $match);

        if (empty($match['phone'])) {
            return $this;
        }

        $phones = [];

        for ($i = 0, $max = count($match['phone']); $i < $max; $i++) {
            $phone = preg_replace("/[^0-9]+/", "", $match['phone'][$i]);
            $ext = preg_replace("/[^0-9]+/", "", $match['ext'][$i]);

            if (in_array($phone . $ext, $phones)) {
                continue;
            }

            $phones[] = $phone . $ext;

            $notes = trim($match['notes'][$i], " \t\n\r\0\x0B,.!");

            $this->result['phones'][] = [
                'number' => $phone,
                'extension' => !empty($ext) ? $ext : null,
                'notes' => !empty($notes) ? $notes : null
            ];
        }

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
