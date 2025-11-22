<?php

namespace App\Traits\Translations;

use App\DTO\NameTranslationDTO;
use Illuminate\Database\Eloquent\Model;

trait TranslationCrud
{
    public function editName(Model $model, $dto): void
    {
        foreach ($dto->getTranslations() as $translation){
            /** @var $translation NameTranslationDTO */
            $t = $model->translations()->where('lang', $translation->getLang())->first();
            $t->name = $translation->getName();
            $t->save();
        }
    }
}
