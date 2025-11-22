<?php

namespace App\Services\Parsers\Drivers\ReadyLogistics\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use function React\Promise\Stream\first;

class PickupDeliveryContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^(?<city>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/";
    private const PATTERN_STREET = "/^[\d]+\s[\w\s]*$/";
    private const PATTERN_PHONE = "/^\(\d{3}\)\s\d{3}-\d{4}$/";
    private const PATTERN_NAME = "/^[a-zA-Z\s]+$/";
    private const PATTERN_ZIP = "/^\d+$/";
    private const PATTERN_CITY_AND_STATE = "/^[a-z]+( [a-z]+)*, [a-z]{2}$/i";

    private string $text;
    private ?string $locationStr;
    private ?string $contactStr;

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
        $this->text = trim($this->replacementIntend($this->replaceBefore($text)));

//        dd($this->text);

        $lines = explode("\n", $this->text);

        foreach ($lines as $k => $line){
            $s = preg_replace('/^\?\?/', '', $line);
            $s = preg_replace('/\?\?$/', '', $s);
            $s = trim($s);

            $lines[$k] = $s;
        }

//        dd($lines);

        if($this->parsingAttribute->name == 'pickup_contact'){
            $this->locationStr = $lines[0];
            $this->contactStr = $lines[1];
        } else {
            $this->locationStr = $lines[2];
            $this->contactStr = $lines[3];
        }

        return $this
            ->setLocation()
            ->setContact()
            ->getCollection();
    }

    private function setContact(): self
    {
        $tmp = explode(" ?? ", $this->contactStr);

        $phone = null;
        $name = null;
        $ext = null;

        foreach ($tmp as $k => $item) {
            if(preg_match("/x\d*/", $item, $match)){
                $ext = trim(preg_replace("/x/", '', current($match)));
                $item = trim(preg_replace("/x\d*/", '', $item));
            }

            if(preg_match(self::PATTERN_PHONE, $item)){
                $phone = phone_clear($item);
                unset($tmp[$k]);
            }
            if($k == 0){
                $name = $item;
                unset($tmp[$k]);
            }
        }

        if(!empty($tmp)){
            $email = implode('', $tmp);
            if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                $this->result['email'] = $email;
            }
        }

        $this->result['phones'][] = [
            'number' => $phone,
            'phone_name' => $name,
            'phone_extension' => $ext,
        ];

        return $this;
    }

    private function setLocation(): self
    {
        $tmp = explode(" ?? ", $this->locationStr);

        // локация - <city, state zip>, может сместиться, zip уйти на вторую строку,
        // если такой кейс есть, вырезаем zip и склеиваем с локацией
        if(
            preg_match(self::PATTERN_ZIP, last($tmp))
            && isset($tmp[count($tmp) - 2])
            && preg_match(self::PATTERN_CITY_AND_STATE, $tmp[count($tmp) - 2])
        ){
            $tmp[count($tmp) - 2] .= ' '. $tmp[count($tmp) - 1];
            unset($tmp[count($tmp) - 1]);
        }

        foreach ($tmp as $k => $item){

            if(preg_match(self::PATTERN_LOCATION, $item, $match)){
                if(
                    isset($match['city'])
                    && isset($match['state'])
                    && isset($match['zip'])
                ){
                    $this->result['city'] = $match['city'];
                    $this->result['state'] = $match['state'];
                    $this->result['zip'] = $match['zip'];
                    unset($tmp[$k]);
                }
            }

            if(preg_match(self::PATTERN_STREET, $item)){
                $this->result['address'] = $item;
                unset($tmp[$k]);
            }
        }

        $this->result['full_name'] = implode(' ', $tmp);

        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
