<?php

namespace Tests\Builders\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Commercial\Commissioning\Question;
use Database\Factories\Commercial\Commissioning\OptionAnswerTranslationFactory;
use Tests\Builders\BaseBuilder;

class OptionAnswerBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return OptionAnswer::class;
    }

    function getModelTranslationFactoryClass(): string
    {
        return OptionAnswerTranslationFactory::class;
    }

    public function withTranslation(): self
    {
        $this->withTranslation = true;

        return $this;
    }

    public function setQuestion(Question $model): self
    {
        $this->data['question_id'] = $model->id;

        return $this;
    }
}
