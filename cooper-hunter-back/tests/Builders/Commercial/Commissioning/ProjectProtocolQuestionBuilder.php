<?php

namespace Tests\Builders\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Models\Commercial\Commissioning\Question;
use Tests\Builders\BaseBuilder;

class ProjectProtocolQuestionBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return ProjectProtocolQuestion::class;
    }

    public function setProjectProtocol(ProjectProtocol $model): self
    {
        $this->data['project_protocol_id'] = $model->id;

        return $this;
    }

    public function setQuestion(Question $model): self
    {
        $this->data['question_id'] = $model->id;
        $this->data['sort'] = $model->sort;

        return $this;
    }

    public function setAnswerStatus($value): self
    {
        $this->data['answer_status'] = $value;

        return $this;
    }
}

