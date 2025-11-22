<?php

namespace App\Repositories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\Answer;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class AnswerRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Answer::query();
    }
}

