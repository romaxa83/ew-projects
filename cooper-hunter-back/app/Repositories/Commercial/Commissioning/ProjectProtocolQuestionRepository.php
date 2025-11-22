<?php

namespace App\Repositories\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Models\Commercial\Commissioning\ProjectProtocolQuestion;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ProjectProtocolQuestionRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return ProjectProtocolQuestion::query();
    }

    public function getAllForRemove($questionID): Collection
    {
        return $this->modelQuery()
            ->where('question_id', $questionID)
            ->where('answer_status', '!=', AnswerStatus::ACCEPT)
            ->get();
    }
}
