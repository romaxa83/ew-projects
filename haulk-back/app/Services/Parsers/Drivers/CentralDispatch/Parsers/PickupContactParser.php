<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PickupContactParser extends ValueParserAbstract
{

    private array $result = [
        'contact_name' => null,
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phone' => null,
        'phones' => null,
        'fax' => null,
        'email' => null
    ];

    public function parse(string $text): Collection
    {
        $text = trim($text);
//dd($text);
        $firstPart = preg_replace('/.*(?=Origin Info)/s', '', $text);
        $secondPart = preg_replace('/Dates.*$/s', '', $firstPart);

        // Разбиваем текст на строки
        $lines = explode("\n", $secondPart);

        $firstColumn = [];
        $secondColumn = [];

        foreach ($lines as $k => $line) {
            // Удаляем лишние пробелы
            $trimmed_line = trim($line);

            // игнорим пустые строки и заглавие
            if (!$trimmed_line) continue;
            if ($k == 0) continue;
            if ($k == 1) continue;

            // получаем значение до первого большого пробела, т.е. первую колонку
            preg_match('/^[^ ]+(?: [^ ]+)*(?= {2,})/', $line, $first);
            if(isset($first[0]) && $first[0] !== '--') {
                $firstColumn[] = $first[0];
            } elseif ($line && !$this->hasLargeSpaces($line)) {
                $firstColumn[] = $line;
            }

            // получаем значение после первого и до второго большого пробела, т.е. вторую колонку
            preg_match('/ [ ]+([^ ]+(?: [^ ]+)*)(?= {2,})/', $line, $second);
            if(isset($second[1]) && $second[1] != '--') $secondColumn[] = $second[1];
        }

        // нормализуем первую колонку
        // удаляем элемент со значение Location Type и все что ниже него
        $indexForSpecialNumber = array_search("Location Type", $firstColumn);
        if ($indexForSpecialNumber !== false) {
            // Если элемент найден, удалим его и все, что следует за ним
            array_splice($firstColumn, $indexForSpecialNumber);
        }

        // нормализуем вторую колонку
        // удаляем элемент со значение Buyer Reference Number и все что ниже него
        $indexForDispatchPhone = array_search("Buyer Reference Number", $secondColumn);
        if ($indexForDispatchPhone !== false) {
            // Если элемент найден, удалим его и все, что следует за ним
            array_splice($secondColumn, $indexForDispatchPhone);
        }

        // разбираем первую колонку
        $str_1 = implode(" ", $firstColumn);

        // addressPattern
//        $patternAddress = '/^(?<street>\d+\w*(?:\s+[NSEW]?\s*[a-zA-Z]+)+)\s+(?<city>[a-zA-Z]+(?:\s+[a-zA-Z]+)*)\s*,\s*(?<state>[A-Z]{2})\s+(?<zipcode>\d{5})$/';
//        $patternAddress = '/^(?<street>\d+\s+[a-zA-Z\s]+)\s*,?\s*(?<city>[a-zA-Z]+(?:\s+[a-zA-Z]+)*)\s*,\s*(?<state>[A-Z]{2})\s*(?<zipcode>\d{5})$/';
        $patternAddress = '/^(?<street>\d+\s+[a-zA-Z0-9\s]+)\s*,?\s+(?<city>[a-zA-Z\s]+?)\s*,\s*(?<state>[A-Z]{2})\s+(?<zipcode>\d{5})$/';

        // парсим адрес где встречается PO Box
        if(preg_match('/po\s*box/i', $str_1)){
            if (preg_match('/^([\w\s]+)\s(po\s*box\s*\d+)\s([^,]+),\s*([A-Z]{2})\s*(\d{5})$/i', $str_1, $matchesStr_1)){
                $this->result['contact_name'] = $matchesStr_1[1] ?? null;
                $this->result['address'] = $matchesStr_1[2] ?? null;
                $this->result['city'] = $matchesStr_1[3] ?? null;
                $this->result['state'] = $matchesStr_1[4] ?? null;
                $this->result['zip'] = $matchesStr_1[5] ?? null;
            }
        } elseif (
            preg_match($patternAddress, $str_1, $matchesAddress)
        ){
            $this->result['address'] = $matchesAddress['street'] ?? null;
            $this->result['city'] = $matchesAddress['city'] ?? null;
            $this->result['state'] = $matchesAddress['state'] ?? null;
            $this->result['zip'] = $matchesAddress['zipcode'] ?? null;
        } else {
            preg_match('/^(.*)\s+(\d{1,5}\s+\D+\s+\S+,\s+[A-Z]{2}\s+\d{5})$/', $str_1, $matchesStr_1);

            if($matchesStr_1){
                $companyName = $this->normalizeString($matchesStr_1[1]);
                $addressPart = $this->normalizeString($matchesStr_1[2]);

                $this->result['contact_name'] = $companyName;

                if(isset($matchesStr_1[2])){
                    preg_match('/^(\d+ [^,]+) ([^,]+), ([A-Z]{2}) (\d{5})$/',$matchesStr_1[2], $matchesAddress);

                    $this->result['address'] = $matchesAddress[1] ?? null;
                    $this->result['city'] = $matchesAddress[2] ?? null;
                    $this->result['state'] = $matchesAddress[3] ?? null;
                    $this->result['zip'] = $matchesAddress[4] ?? null;
                }
            }
        }

        // разбираем вторую колонку
        $str_2 = implode(" ", $secondColumn);

        // только имя
        if(preg_match('/^[a-zA-Z\s]+$/', $str_2)){

            $this->result['full_name'] = $str_2;
        } else {

            // Регулярное выражение для поиска телефонного номера в формате (###) ###-####
            $patternPhone = '/\(\d{3}\) \d{3}-\d{4}/';

            preg_match($patternPhone, $str_2, $matchesPhone);
            $this->result['phone'] = $matchesPhone[0] ?? null;

            // Регулярное выражение для поиска имени, которое идет перед телефонным номером
            $patternName = '/^(.*)\s+\(\d{3}\) \d{3}-\d{4}/';

            preg_match($patternName, $str_2, $matchesName);
            $this->result['full_name'] = $matchesName[1] ?? null;


            $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
            preg_match($emailPattern, $str_2, $matchesEmail);
            $this->result['email'] = $matchesEmail[0] ?? null;

        }
//        dd($firstColumn, $secondColumn, $this->result);

        if($this->result['phone']){
            $this->result['phone'] = '1'.$this->result['phone'];
        }

        return collect($this->result);
    }

    function normalizeString($string) {
        return rtrim($string, ', ');
    }

    public function hasLargeSpaces($string) {
        return preg_match('/\s{2,}/', $string);
    }
}
