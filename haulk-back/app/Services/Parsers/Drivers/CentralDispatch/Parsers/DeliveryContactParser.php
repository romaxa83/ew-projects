<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class DeliveryContactParser extends ValueParserAbstract
{

    const EMAIL_PATTERN = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';

    private array $result = [
        'contact_name' => null,
        'full_name' => null,
        'state' => null,
        'city' => null,
        'address' => null,
        'zip' => null,
        'phones' => null,
        'phone' => null,
        'fax' => null,
        'email' => null
    ];

    public function parse(string $text): Collection
    {
        $text = trim($text);

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

            // Ищем все последовательности из двух и более пробелов
            $patternBigSpace = '/\s{2,}/';
            preg_match_all($patternBigSpace, $line, $matches);

            if(count($matches[0]) == 3){
                $parts = preg_split('/\s{2,}/', $line);

                $firstColumn[] = $parts[2];
                $secondColumn[] = $parts[3];

            } elseif (count($matches[0]) == 2) {
                $parts = preg_split('/\s{2,}/', $line);
                $numSpaces = strlen($matches[0][0]);
                if($numSpaces > 48 && $numSpaces < 70){
                    $firstColumn[] = $parts[1];
                }

                if(preg_match(self::EMAIL_PATTERN, last($parts), $matchesEmail)){
                    $secondColumn[] = $matchesEmail[0] ?? null;

                }

                $firstColumn[] = $parts[2];
            } elseif (count($matches[0]) == 1) {
                $numSpaces = strlen($matches[0][0]);

                if($numSpaces > 45 && $numSpaces < 75){
                    $parts = preg_split('/\s{2,}/', $line);
                    $firstColumn[] = $parts[1];
                } else {
//                    $parts = preg_split('/\s{2,}/', $line);
//                    dd($line, $numSpaces, $parts);
                }
            }
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

        $patternState = '/,\s([A-Z]{2})(?=\s\d+)/';
        preg_match($patternState, $str_1, $matchesState);
        $this->result['state'] = $matchesState[1] ?? null;

        $patternZip = '/,\s[A-Z]{2}\s(\d{4,})/';
        preg_match($patternZip, $str_1, $matchesZip);
        $this->result['zip'] = $matchesZip[1] ?? null;

        $patternAddress = '/\b(\d.*?),/';
        preg_match($patternAddress, $str_1, $matchesAddress);

        if(isset($matchesAddress[1])){
            $parts = explode(' ',$matchesAddress[1]);

            $city = array_pop($parts);

            $this->result['address'] = implode(" ", $parts);
            $this->result['city'] = $city;
        }

        $patternName = '/^(.*?)(?=\s\d)/';
        preg_match($patternName, $str_1, $matchesName);
        $this->result['contact_name'] = isset($matchesName[1]) && $matchesName[1] !== '--'
            ? $matchesName[1]
            : null;


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
            $this->result['full_name'] = isset($matchesName[1]) && $matchesName[1] !== '--'
                ? $matchesName[1]
                : null;


            $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
            preg_match($emailPattern, $str_2, $matchesEmail);
            $this->result['email'] = $matchesEmail[0] ?? null;

        }
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
