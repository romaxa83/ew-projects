<?php

namespace App\Services\Parsers\Drivers\ReadyLogistics\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ShipperContactParser extends ValueParserAbstract
{
    private const PATTERN_LOCATION = "/^(?<city>[^,]+),\s+(?<state>[A-Z]{2})\s+(?<zip>[0-9]+)$/";

    private const PATTERN_STREET = "/^[\d]+\s[\w\s\/.]*$/";
    private const PATTERN_PHONE = "/^\(\d{3}\)\s\d{3}-\d{4}$/";

    private string $text;

    private ?string $locationStr;
    private ?string $contactStr;

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
        $this->text = $this->replaceBefore($text);

        $lines = explode("\n", $this->text);

        foreach ($lines as $k => $line){

            $s = trim($line);
            $s = preg_replace('/^\?\?/', '', $s);
            $s = preg_replace('/\?\?$/', '', $s);
            $s = trim($s);

            $lines[$k] = $s;
        }

        $this->locationStr = $lines[0];
        $this->contactStr = $lines[1];

        return $this
            ->setLocation()
            ->setContact()
            ->getResult();
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

            if(preg_match('/^[\d]*$/', $item)){
                $this->result['address'] .= ' ' . $item;
                unset($tmp[$k]);
            }
        }

        $this->result['full_name'] = implode(' ', $tmp);

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
