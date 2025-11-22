<?php

namespace App\Services\Localizations;

use App\Models\Hash;
use App\Services\Localizations\Export\ExportTranslation;
use DB;
use App\Models\Localization\Translation;
use App\Repositories\Localization\TranslationRepository;
use Illuminate\Database\Eloquent\Collection;

class TranslationService
{
    public function __construct(private TranslationRepository $translationRepository)
    {}

    public function createOrUpdate(array $translations): void
    {
        DB::beginTransaction();
        try {
            $place = $translations['place'] ?? null;
            $group = $translations['group'] ?? null;

            Translation::assetPLace($place);

            foreach ($translations['translations'] ?? [] as $items){
                $key = $items['key'];
                // если по ключу нет переводов, то удаляем из бд данные по этому ключу
                if(empty($items['translation'])){
                    $trans = $this->translationRepository->getByPlaceAndKey(
                        $place,
                        $key,
                        $group);
                    $this->remove($trans);
                } else {
                    foreach ($items['translation'] ?? [] as $translation){
                        $lang = $translation['lang'];
                        $this->createOrUpdateOneRow($place, $lang, $key, $translation['text'], $group);
                    }
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function createOrUpdateOneRow($place, $lang, $key, $text, $group = null, bool $update = true): Translation
    {
        /** @var $t Translation */
        if($t = $this->translationRepository->getByPlaceAndKeyAndLang($place, $key, $lang)){
            if($update){
                $t->text = $text;
                $t->save();

                // @todo подумать о выносе в очереди или обновление только данного переводы
                if($t->isSystem()){
                    $export = app(ExportTranslation::class);
                    $export->exportByGroup($t->group);
                }
            }
        } else {
            $t = $this->createRow($place, $lang, $key, $text, $group);
        }

        return $t;
    }

    public function createRow($place, $lang, $key, $text, $group = null): Translation
    {
        $t = new Translation();
        $t->place = $place;
        $t->key = $key;
        $t->lang = $lang;
        $t->text = $text;
        $t->group = $group;
        $t->save();

        return $t;
    }

    public function getHashByPlace(string $place): string
    {
        return Hash::hash($this->translationRepository->getByPlaceAsArray($place));
    }

    public function remove(Collection $models): void
    {
        foreach ($models as $model){
            /** @var $model Translation */
            $model->forceDelete();
        }
    }

    public function removeByPlaceAndOrKey($place, $key): void
    {
        $trans = $this->translationRepository->getByPlaceAndOrKey($place, $key);
        $this->remove($trans);
    }
}
