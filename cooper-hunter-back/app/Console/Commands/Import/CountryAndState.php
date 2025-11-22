<?php

namespace App\Console\Commands\Import;

use App\Models\Locations\Country;
use App\Models\Locations\CountryTranslation;
use App\Models\Locations\State;
use App\Models\Locations\StateTranslation;
use App\Repositories\Locations\CountryRepository;
use App\Repositories\Locations\StateRepository;
use Illuminate\Console\Command;

class CountryAndState extends Command
{
    protected $signature = 'import:country';

    protected $description = 'Загрузка стран и штатов(регионов  Канады), привязка штатов к США';

    public function __construct(
        protected CountryRepository $countryRepository,
        protected StateRepository $stateRepository
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->setCountries();
        $this->attachStateToUsa();
        $this->setStateCanada();
    }

    private function setCountries()
    {
        $count = 0;
        foreach ($this->countriesData() as $item){
            if(!$this->countryRepository->existBy(['alias' => $item['alias']])){
                $model = new Country();
                $model->alias = $item['alias'];
                $model->default = $item['default'];
                $model->country_code = $item['country_code'];
                $model->save();
                $count++;
                foreach ($item['translations'] as $lang => $name) {
                    $t = new CountryTranslation();
                    $t->row_id = $model->id;
                    $t->language = $lang;
                    $t->name = $name;
                    $t->save();
                }
            }
        }
        $this->info("Set [{$count}] countries");
    }

    private function countriesData()
    {
        return [
            [
                'alias' => 'usa',
                'default' => true,
                'country_code' => 'US',
                'translations' => [
                    'en' => 'United States of America',
                    'es' => 'United States of America',
                ]
            ],
            [
                'alias' => 'canada',
                'default' => false,
                'country_code' => 'CA',
                'translations' => [
                    'en' => 'Canada',
                    'es' => 'Canada',
                ]
            ]
        ];
    }

    private function attachStateToUsa()
    {
        $usa = Country::query()->where('alias', 'usa')->first();
        $states = State::query()->whereNull('country_id')->get();

        $count = 0;
        foreach ($states as $model){
            /** @var $model State */
            $model->update(['country_id' => $usa->id]);
            $count++;
        }

        $this->info("Attach state [{$count}] to usa");
    }

    private function setStateCanada()
    {
        $canada = Country::query()->where('alias', 'canada')->first();

        $count = 0;
        foreach ($this->stateData() as $item){
            if(!$this->stateRepository->existBy(['short_name' => $item['short_name']])){
                $model = new State();
                $model->short_name = $item['short_name'];
                $model->country_id = $canada->id;
                $model->epa_license = $item['epa_license'];
                $model->save();

                $count++;
                foreach ($item['translations'] as $lang => $name) {
                    $t = new StateTranslation();
                    $t->row_id = $model->id;
                    $t->language = $lang;
                    $t->name = $name;
                    $t->save();
                }
            }
        }
        $this->info("Set state [{$count}] to canada");
    }

    private function stateData()
    {
        return [
            [
                'short_name' => 'CA-AB',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Alberta',
                    'es' => 'Alberta',
                ]
            ],
            [
                'short_name' => 'CA-BC',
                'epa_license' => true,
                'translations' => [
                    'en' => 'British Columbia',
                    'es' => 'British Columbia',
                ]
            ],
            [
                'short_name' => 'CA-MB',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Manitoba',
                    'es' => 'Manitoba',
                ]
            ],
            [
                'short_name' => 'CA-NB',
                'epa_license' => true,
                'translations' => [
                    'en' => 'New Brunswick',
                    'es' => 'New Brunswick',
                ]
            ],
            [
                'short_name' => 'CA-NL',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Newfoundland and Labrador',
                    'es' => 'Newfoundland and Labrador',
                ]
            ],
            [
                'short_name' => 'CA-NT',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Northwest Territories',
                    'es' => 'Northwest Territories',
                ]
            ],
            [
                'short_name' => 'CA-NS',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Nova Scotia',
                    'es' => 'Nova Scotia',
                ]
            ],
            [
                'short_name' => 'CA-NU',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Nunavut',
                    'es' => 'Nunavut',
                ]
            ],
            [
                'short_name' => 'CA-ON',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Ontario',
                    'es' => 'Ontario',
                ]
            ],
            [
                'short_name' => 'CA-PE',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Prince Edward Island',
                    'es' => 'Prince Edward Island',
                ]
            ],
            [
                'short_name' => 'CA-QC',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Quebec',
                    'es' => 'Quebec',
                ]
            ],
            [
                'short_name' => 'CA-SK',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Saskatchewan',
                    'es' => 'Saskatchewan',
                ]
            ],
            [
                'short_name' => 'CA-YT',
                'epa_license' => true,
                'translations' => [
                    'en' => 'Yukon',
                    'es' => 'Yukon',
                ]
            ],
        ];
    }
}

