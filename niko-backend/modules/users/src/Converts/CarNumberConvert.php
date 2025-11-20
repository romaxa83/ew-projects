<?php

namespace WezomCms\Users\Converts;

use WezomCms\Core\UseCase\DateFormatter;

class CarNumberConvert
{
    public static function for1c($number)
    {
        if($number){
            $number = str_replace('-', '', str_replace(' ', '', trim(mb_strtoupper($number))));
            $newNumber = '';

            foreach (preg_split('//u',$number,-1,PREG_SPLIT_NO_EMPTY) as $letter){
                if(is_numeric($letter)){
                    $newNumber .= $letter;
                } else {
                    $newNumber .= self::change($letter);
                }
            }

            return $newNumber;
        }

        return null;
    }

    private static function change(string $letter): string
    {
        $data = [
            'А' => 'A',
            'В' => 'B',
            'Е' => 'E',
            'Ё' => 'E',
            'Є' => 'E',
            'Э' => 'E',
            'К' => 'K',
            'М' => 'M',
            'Н' => 'H',
            'У' => 'Y',
            'Х' => 'X',
            'Р' => 'P',
            'О' => 'O',
            'С' => 'C',
            'Т' => 'T',
            'І' => 'I',
            'И' => 'I',
        ];

        if(array_key_exists($letter, $data)){
            $letter = $data[$letter];
        }

        return $letter;
    }
}
