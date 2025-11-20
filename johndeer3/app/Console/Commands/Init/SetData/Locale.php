<?php

namespace App\Console\Commands\Init\SetData;

use App\DTO\Locale\LanguageDTO;
use App\Repositories\LanguageRepository;
use App\Services\Locale\LanguageService;

class Locale
{
    private $languageRepository;
    private $languageService;

    public function __construct(
        LanguageRepository $languageRepository,
        LanguageService $languageService
    )
    {
        $this->languageRepository = $languageRepository;
        $this->languageService = $languageService;
    }

    public function run(): void
    {

        $this->setLocales();
    }

    private function setLocales(): void
    {
        foreach ($this->data() as $item){
            if(!$this->languageRepository->existBy('slug', $item['slug'])){
                $this->languageService->create(LanguageDTO::byArgs([
                    "name" => $item["name"],
                    "native" => $item["native"],
                    "slug" => $item["slug"],
                    "locale" => $item["locale"],
                    "default" => $item["default"],
                ]));


                echo "Set language - [{$item["name"]}]" . PHP_EOL;
            }
        }
    }

    private function data(): array
    {
        return [
            [
                'name' => 'English',
                'native' => 'English',
                'slug' => 'en',
                'locale' => 'en_EN',
                'default' => true
            ],
            [
                'name' => 'Ukrainian',
                'native' => 'Український',
                'slug' => 'ua',
                'locale' => 'uk_UA',
                'default' => false
            ],
            [
                'name' => 'Russian',
                'native' => 'Русский',
                'slug' => 'ru',
                'locale' => 'ru_RU',
                'default' => false
            ],
            [
                'name' => 'Bulgarian',
                'native' => 'Български',
                'slug' => 'bg',
                'locale' => 'bg_BG',
                'default' => false
            ],
            [
                'name' => 'Czech',
                'native' => 'čeština',
                'slug' => 'cz',
                'locale' => 'cs_CZ',
                'default' => false
            ],
            [
                'name' => 'German',
                'native' => 'Deutsch',
                'slug' => 'de',
                'locale' => 'de_DE',
                'default' => false
            ],
            [
                'name' => 'Danish',
                'native' => 'dansk',
                'slug' => 'da',
                'locale' => 'da_DK',
                'default' => false
            ],
            [
                'name' => 'Estonian',
                'native' => 'eesti',
                'slug' => 'et',
                'locale' => 'et_EE',
                'default' => false
            ],
            [
                'name' => 'Spanish',
                'native' => 'español',
                'slug' => 'es',
                'locale' => 'es_ES',
                'default' => false
            ],
            [
                'name' => 'Finnish',
                'native' => 'suomi',
                'slug' => 'fi',
                'locale' => 'fi_FI',
                'default' => false
            ],
            [
                'name' => 'French',
                'native' => 'français',
                'slug' => 'fr',
                'locale' => 'fr_FR',
                'default' => false
            ],
            [
                'name' => 'Greek',
                'native' => 'Ελληνικά',
                'slug' => 'el',
                'locale' => 'el_GR',
                'default' => false
            ],
            [
                'name' => 'Croatian',
                'native' => 'hrvatski',
                'slug' => 'hr',
                'locale' => 'hr_HR',
                'default' => false
            ],
            [
                'name' => 'Hungarian',
                'native' => 'magyar',
                'slug' => 'hu',
                'locale' => 'hu_HU',
                'default' => false
            ],
            [
                'name' => 'Italian',
                'native' => 'italiano',
                'slug' => 'it',
                'locale' => 'it_IT',
                'default' => false
            ],
            [
                'name' => 'Lithuanian',
                'native' => 'lietuvių',
                'slug' => 'lt',
                'locale' => 'lt_LT',
                'default' => false
            ],
            [
                'name' => 'Latvian',
                'native' => 'latviešu',
                'slug' => 'lv',
                'locale' => 'lv_LV',
                'default' => false
            ],
            [
                'name' => 'Dutch',
                'native' => 'Nederlands',
                'slug' => 'nl',
                'locale' => 'nl_NL',
                'default' => false
            ],
            [
                'name' => 'Norwegian',
                'native' => 'nynorsk',
                'slug' => 'nn',
                'locale' => 'nn_NO',
                'default' => false
            ],
            [
                'name' => 'Polish',
                'native' => 'polski',
                'slug' => 'pl',
                'locale' => 'pl_PL',
                'default' => false
            ],
            [
                'name' => 'Romania',
                'native' => 'română',
                'slug' => 'ro',
                'locale' => 'ro_RO',
                'default' => false
            ],
            [
                'name' => 'Serbian',
                'native' => 'Srpski',
                'slug' => 'sr',
                'locale' => 'sr_RS',
                'default' => false
            ],
            [
                'name' => 'Swedish',
                'native' => 'svenska',
                'slug' => 'sv',
                'locale' => 'sv_SE',
                'default' => false
            ],
            [
                'name' => 'Slovakian',
                'native' => 'slovenčina',
                'slug' => 'sk',
                'locale' => 'sk_SK',
                'default' => false
            ],
            [
                'name' => 'Portuguese',
                'native' => 'português',
                'slug' => 'pt',
                'locale' => 'pt_PT',
                'default' => false
            ],
        ];
    }
}


