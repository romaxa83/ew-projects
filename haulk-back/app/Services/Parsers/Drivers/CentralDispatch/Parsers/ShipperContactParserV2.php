<?php


namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParserV2 extends ValueParserAbstract
{
    private string $text;

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

        $firstPart = preg_replace('/.*(?=Shipper Info)/s', '', $text);
        $secondPart = preg_replace('/Origin Info.*$/s', '', $firstPart);

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
            if(isset($first[0])) $firstColumn[] = $first[0];

            // получаем значение после первого и до второго большого пробела, т.е. вторую колонку
            preg_match('/ [ ]+([^ ]+(?: [^ ]+)*)(?= {2,})/', $line, $second);
            if(isset($second[1])) $secondColumn[] = $second[1];
        }

        // нормализуем первую колонку
        // удаляем элемент со значение Special Number и все что ниже него
        $indexForSpecialNumber = array_search("Special Number", $firstColumn);
        if ($indexForSpecialNumber !== false) {
            // Если элемент найден, удалим его и все, что следует за ним
            array_splice($firstColumn, $indexForSpecialNumber);
        }

        // нормализуем вторую колонку
        // удаляем элемент со значение Dispatch Phone и все что ниже него
        $indexForDispatchPhone = array_search("Dispatch Phone", $secondColumn);
        if ($indexForDispatchPhone !== false) {
            // Если элемент найден, удалим его и все, что следует за ним
            array_splice($secondColumn, $indexForDispatchPhone);
        }

        // в первой колонки может быть дублирующее значение
        foreach ($firstColumn as $index => $column) {
            $firstColumn[$index] = rtrim($column, ',');
        }
        $firstColumn = array_values(array_unique($firstColumn));

        // разбираем первую колонку
        $str_1 = implode(" ", $firstColumn);

        // парсим адрес где встречается PO Box
        if(preg_match('/po\s*box/i', $str_1)){
            if (preg_match('/^([\w\s]+)\s(po\s*box\s*\d+)\s([^,]+),\s*([A-Z]{2})\s*(\d{5})$/i', $str_1, $matchesStr_1)){
                $this->result['contact_name'] = $matchesStr_1[1] ?? null;
                $this->result['address'] = $matchesStr_1[2] ?? null;
                $this->result['city'] = $matchesStr_1[3] ?? null;
                $this->result['state'] = $matchesStr_1[4] ?? null;
                $this->result['zip'] = $matchesStr_1[5] ?? null;
            }
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

        preg_match('/^([\w\s,]+)\s*\((\d{3})\)\s*(\d{3}-\d{4})$/', $str_2, $matchesStr_2);

        $this->result['full_name'] = $matchesStr_2[1] ?? null;

        if(isset($matchesStr_2[2]) && isset($matchesStr_2[3])){
            $phone = sprintf("(%s) %s", $matchesStr_2[2], $matchesStr_2[3]);
            $this->result['phone'] = $phone;
        }

        $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        preg_match($emailPattern, $str_2, $matchesEmail);
        $this->result['email'] = $matchesEmail[0] ?? null;

        if($this->result['phone']){
            $this->result['phone'] = '1'.$this->result['phone'];
        }

        return collect($this->result);
    }

    function normalizeString($string) {
        return rtrim($string, ', ');
    }
}
