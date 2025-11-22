<?php

namespace App\Console\Commands\Translates;

use App\Models\Localization\Language;
use App\Models\Localization\Translation;
use App\Repositories\Localization\TranslationRepository;
use App\Services\Localizations\LocalizationService;
use App\Services\Localizations\TranslationService;
use DB;
use Illuminate\Console\Command;

class Copy extends Command
{
    protected $signature = 'am:translates-copy';

    protected $description = 'Copy translations from from default lang to other lan';

    public function __construct(
        protected LocalizationService $localizationService,
        protected TranslationRepository $repository,
        protected TranslationService $service
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            DB::beginTransaction();
            try {
                $count = Translation::count();
                $this->info('Корреляция перевод для других языков (если для них не перевода)');

                $defaultLangForCopy = Language::DEFAULT_FOR_COPY;
                $langs = $this->localizationService->getLocalesAsArray();
                $translations = $this->repository->getByLang($defaultLangForCopy);

                foreach ($translations as $item){
                    /** @var $item Translation */
                    foreach ($langs as $lang){
                        if($defaultLangForCopy !== $lang){

                            if(!$this->checkExist($item, $lang)){
                                $this->service->createRow(
                                    $item->place,
                                    $lang,
                                    $item->key,
                                    $item->text . " __(translate into {$lang})",
                                    $item->group
                                );
                            }
                        }
                    }
                }

                $countNew = Translation::count();
                $add = $countNew - $count;

                $this->info("Кол-во перед корреляцией - {$count}, после - {$countNew}, добавлено - {$add}");
                $this->info('Done');

                DB::commit();

            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function checkExist(Translation $model, $lang): bool
    {
        $q = Translation::query()
            ->where('place', $model->place)
            ->where('key', $model->key)
            ->where('lang', $lang);

        if($model->group){
            $q->where('group', $model->group);
        }

        return $q->exists();
    }
}
