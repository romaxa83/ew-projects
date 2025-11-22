<?php

namespace Tests\Builders\Catalog;

use App\Models\Catalog\Labels\Label;
use Database\Factories\Catalog\Labels\LabelTranslationFactory;
use Tests\Builders\BaseBuilder;

class LabelBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return Label::class;
    }

    function getModelTranslationFactoryClass(): string
    {
        return LabelTranslationFactory::class;
    }

    public function withTranslation(): self
    {
        $this->withTranslation = true;

        return $this;
    }
}
