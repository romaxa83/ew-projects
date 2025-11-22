<?php

namespace Tests\Builders\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\Answer;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use Tests\Builders\BaseBuilder;

class AnswerBuilder extends BaseBuilder
{
    protected array $optionAnswerIds = [];

    protected function modelClass(): string
    {
        return Answer::class;
    }

    public function setProjectProtocolQuestion(ProjectProtocolQuestion $model): self
    {
        $this->data['project_protocol_question_id'] = $model->id;

        return $this;
    }

    public function setText(string $value): self
    {
        $this->data['text'] = $value;

        return $this;
    }

    public function setRadio(bool $value): self
    {
        $this->data['radio'] = $value;

        return $this;
    }

    public function setOptionAnswers(...$data): self
    {
        foreach ($data as $model){
            $this->optionAnswerIds[] = $model->id;
        }

        return $this;
    }

    protected function afterSave($model): void
    {
        $model->optionAnswers()->attach($this->optionAnswerIds);
    }

    protected function afterClear(): void
    {
        $this->optionAnswerIds = [];
    }

}

