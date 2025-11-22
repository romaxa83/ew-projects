<?php

namespace App\Repositories\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\Question;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ProjectProtocolRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return ProjectProtocol::query();
    }

    public function getAllForAttachQuestion(Question $question): Collection
    {
        return $this->modelQuery()
            ->with(['projectQuestions'])
            ->where('protocol_id', $question->protocol->id)
            ->where('status', '!=', ProtocolStatus::DONE)
            ->whereHas('projectQuestions', fn($b) => $b->where('question_id', '!=', $question->id))
            ->get();
    }
}
