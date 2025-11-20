<?php

namespace App\Console\Commands\Translation;

use App\Models\Translate;
use App\Repositories\LanguageRepository;
use App\Repositories\TranslationRepository;
use App\Services\Translations\TranslationService;
use Illuminate\Console\Command;

class SetByAlias extends Command
{
    protected $signature = 'cmd:set-translations';

    protected $description = 'Загрузка переводов (только алиас и локаль)';

    public function __construct(
        protected LanguageRepository $languageRepository,
        protected TranslationRepository $translateRepository,
        protected TranslationService $translateService
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $locales = $this->languageRepository->getForSelect();

        $count = 0;
        foreach ($this->data() as $alias){
            foreach ($locales as $locale => $lang){
                if(!$this->translateRepository->existByAliasAndLang($alias, $locale)){
                    $this->translateService->save(
                        Translate::TYPE_SITE,
                        null,
                        null,
                        "{$alias} __(translate into {$lang})",
                        $locale,
                        $alias
                    );
                    $count++;
                }
            }
        }

        $this->info("Set [{$count}] alias");
    }

    private function data(): array
    {
        return [
            'model_description.type',
            'model_description.size',
            'model_description.size_parameters',
            'Top level statistic',
            'Statistic type',
            'Statistic size',
            'Statistic crop'
        ];
    }
}



