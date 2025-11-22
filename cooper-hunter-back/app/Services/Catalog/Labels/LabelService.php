<?php

namespace App\Services\Catalog\Labels;

use App\Dto\Catalog\Labels\LabelDto;
use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Labels\LabelTranslation;

class LabelService
{
    public function create(LabelDto $dto): Label
    {
        $model = new Label();
        $model->color_type = $dto->colorType;
        $model->save();

        foreach ($dto->getTranslations() as $translation){
            /** @var $translation SimpleTranslationDto */
            $t = new LabelTranslation();
            $t->title = $translation->getTitle();
            $t->language = $translation->getLanguage();
            $t->row_id = $model->id;
            $t->save();
        }

        return $model;
    }

    public function update(Label $model, LabelDto $dto): Label
    {
        $model->color_type = $dto->colorType;
        $model->save();

        foreach ($dto->getTranslations() as $translation){
            /** @var $translation SimpleTranslationDto */
            $t = $model->translations->where('language', $translation->getLanguage())->first();
            $t->title = $translation->getTitle();
            $t->save();
        }

        return $model;
    }

    public function delete(Label $model): bool
    {
        return $model->delete();
    }
}
