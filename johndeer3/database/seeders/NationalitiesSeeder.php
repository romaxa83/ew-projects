<?php

namespace Database\Seeders;

use App\Models\User\Nationality;
use Illuminate\Database\Seeder;

class NationalitiesSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('nationalities')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Nationality::insertOrIgnore($this->data());
    }

    protected function data(): array
    {
        return [
            ['alias' => 'AL', 'name' => 'Albanian',],
            ['alias' => 'AM', 'name' => 'Armenian',],
            ['alias' => 'AT', 'name' => 'German',],
            ['alias' => 'AZ', 'name' => 'Azerbaijani',],
            ['alias' => 'BY', 'name' => 'Belarusian',],
            ['alias' => 'BE', 'name' => 'Dutch',],
            ['alias' => 'BG', 'name' => 'Bulgarian',],
            ['alias' => 'HR', 'name' => 'Croatian',],
            ['alias' => 'CY', 'name' => 'Greek',],
            ['alias' => 'CZ', 'name' => 'Czech',],
            ['alias' => 'DK', 'name' => 'Danish',],
            ['alias' => 'EE', 'name' => 'Estonian',],
            ['alias' => 'FI', 'name' => 'Finnish',],
            ['alias' => 'FR', 'name' => 'French',],
            ['alias' => 'DE', 'name' => 'German',],
            ['alias' => 'GR', 'name' => 'Greek',],
            ['alias' => 'HU', 'name' => 'Hungarian',],
            ['alias' => 'IS', 'name' => 'Icelandic',],
            ['alias' => 'IE', 'name' => 'Irish',],
            ['alias' => 'IL', 'name' => 'Hebrew',],
            ['alias' => 'IT', 'name' => 'Italian',],
            ['alias' => 'KZ', 'name' => 'Kazakh',],
            ['alias' => 'LV', 'name' => 'Latvian',],
            ['alias' => 'LT', 'name' => 'Lithuanian',],
            ['alias' => 'LU', 'name' => 'French',],
            ['alias' => 'MD', 'name' => 'Moldovan',],
            ['alias' => 'NL', 'name' => 'Dutch',],
            ['alias' => 'MK', 'name' => 'Makedonski',],
            ['alias' => 'NO', 'name' => 'Norwegian',],
            ['alias' => 'PL', 'name' => 'Polish',],
            ['alias' => 'PT', 'name' => 'Portuguese',],
            ['alias' => 'RO', 'name' => 'Romanian',],
            ['alias' => 'RS', 'name' => 'Serbian',],
            ['alias' => 'SK', 'name' => 'Slovakian',],
            ['alias' => 'SI', 'name' => 'Slovenian',],
            ['alias' => 'ES', 'name' => 'Spanish',],
            ['alias' => 'SE', 'name' => 'Swedish',],
            ['alias' => 'CH', 'name' => 'French',],
            ['alias' => 'TR', 'name' => 'Turkish',],
            ['alias' => 'TM', 'name' => 'Turkmen',],
            ['alias' => 'EN', 'name' => 'English',],
            ['alias' => 'UZ', 'name' => 'Uzbek',],
            ['alias' => 'RU', 'name' => 'Russian',],
            ['alias' => 'UA', 'name' => 'Ukraine',],
            ['alias' => 'GE', 'name' => 'Georgian',],
        ];
    }
}
