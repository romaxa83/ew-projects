<?php

namespace WezomCms\Translates\Services;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Models\Translation;

class TranslatesService
{
    public function fillOrUpdate(array $data)
    {
        foreach($data ?? [] as $key => $item){
            foreach ($item ?? [] as $lang => $value){

                if ($model = Translation::query()
                    ->where('namespace', Translation::API_NAMESPACE)
                    ->where('side', Translation::SIDE_SITE)
                    ->where('key', $key)
                    ->where('locale', $lang)
                    ->first()
                ){
                    $model->text = $value;
                    $model->save();
                } else {
                    $model = new Translation();
                    $model->namespace = Translation::API_NAMESPACE;
                    $model->side = Translation::SIDE_SITE;
                    $model->key = $key;
                    $model->locale = $lang;
                    $model->text = $value;
                    $model->save();
                }
            }
        }
    }

    public function remove(Collection $translates)
    {
        foreach ($translates as $model){
            $model->delete();
        }
    }
}
