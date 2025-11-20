<?php

namespace App\Services\FcmNotification;

use App\Models\Notification\FcmTemplate;
use App\Models\Notification\FcmTemplateTranslation;
use App\Repositories\LanguageRepository;
use DB;

class TemplateService
{
    /**
     * @var LanguageRepository
     */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function create(array $data): FcmTemplate
    {
        DB::beginTransaction();

        try {
            $model = new FcmTemplate();
            $model->type = $data['type'];
            $model->vars = $data['vars'];
            $model->save();

            foreach ($data['translations'] ?? [] as $lang => $item){
                $t = new FcmTemplateTranslation();
                $t->model_id = $model->id;
                $t->lang = $lang;
                $t->title = $item['title'];
                $t->text = $item['text'];
                $t->save();
            }


            DB::commit();

            return $model;
        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());

            throw new \Exception($exception->getMessage());
        }
    }

    public function edit(array $data, FcmTemplate $model): FcmTemplate
    {
        $langs = $this->languageRepository->getForSelect();

        DB::beginTransaction();
        try {
            foreach ($data['translations'] ?? [] as $lang => $item){
                if(array_key_exists($lang, $langs)){
                    if($t = $model->translations()->where('lang', $lang)->first()){
                        $t->title = $item['title'];
                        $t->text = $item['text'];
                        $t->save();
                    } else {
                        $t = new FcmTemplateTranslation();
                        $t->model_id = $model->id;
                        $t->lang = $lang;
                        $t->title = $item['title'];
                        $t->text = $item['text'];
                        $t->save();
                    }
                }
            }
            DB::commit();

            return $model;
        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());

            throw new \Exception($exception->getMessage());
        }
    }
}
