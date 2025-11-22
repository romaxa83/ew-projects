<?php

namespace App\Services;

use App\DTO\NameTranslationDTO;
use Illuminate\Database\Eloquent\Model;

class BaseService
{
    public function toggleActive(Model $model): Model
    {
        try {
            $model->hasAppended('active');

            $model->active = !$model->active;
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    protected function editTranslationsName(Model $model, $dto): void
    {
        foreach ($dto->getTranslations() ?? [] as $translation){
            /** @var $translation NameTranslationDTO */
            $t = $model->translations()->where('lang', $translation->getLang())->first();
            $t->name = $translation->getName();
            if(isset($t->text)){
                $t->text = $translation->getText();
            }
            $t->save();
        }
    }
}
