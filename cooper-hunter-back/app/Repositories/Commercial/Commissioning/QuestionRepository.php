<?php

namespace App\Repositories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\Question;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class QuestionRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Question::query();
    }
}
