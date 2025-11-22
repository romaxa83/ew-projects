<?php

namespace Tests\Builders\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\Question;
use Database\Factories\Commercial\Commissioning\QuestionTranslationFactory;
use Tests\Builders\BaseBuilder;

class QuestionBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return Question::class;
    }

    function getModelTranslationFactoryClass(): string
    {
        return QuestionTranslationFactory::class;
    }

    public function setProtocol(Protocol $model): self
    {
        $this->data['protocol_id'] = $model->id;

        return $this;
    }

    public function setStatus(QuestionStatus $status): self
    {
        $this->data['status'] = $status;

        return $this;
    }

    public function setAnswerType($value): self
    {
        $this->data['answer_type'] = $value;

        return $this;
    }

    public function setPhotoType($value): self
    {
        $this->data['photo_type'] = $value;

        return $this;
    }

    public function setSort($value): self
    {
        $this->data['sort'] = $value;

        return $this;
    }
}
