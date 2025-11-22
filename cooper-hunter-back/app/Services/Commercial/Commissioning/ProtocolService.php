<?php

namespace App\Services\Commercial\Commissioning;

use App\Dto\Commercial\Commissioning\ProtocolDto;
use App\Dto\SimpleTranslationDto;
use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\ProtocolTranslation;
use App\Services\BaseService;

class ProtocolService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create(ProtocolDto $dto): Protocol
    {
        $model = new Protocol();
        $model->type = $dto->type;
        $model->save();

        foreach ($dto->getTranslations() as $translation) {
            /** @var $translation SimpleTranslationDto */
            $t = new ProtocolTranslation();
            $t->title = $translation->getTitle();
            $t->desc = $translation->getDescription();
            $t->language = $translation->getLanguage();
            $t->row_id = $model->id;
            $t->save();
        }

        return $model;
    }

    public function update(Protocol $model, ProtocolDto $dto): Protocol
    {
        $model->type = $dto->type;
        $model->save();

        foreach ($dto->getTranslations() as $translation) {
            /** @var $translation SimpleTranslationDto */
            /** @var $t ProtocolTranslation */
            $t = $model->translations()->where('language', $translation->getLanguage())->first();
            $t->title = $translation->getTitle();
            $t->desc = $translation->getDescription();
            $t->save();
        }

        return $model;
    }

    public function delete(Protocol $model): bool
    {
        return $model->delete();
    }
}

