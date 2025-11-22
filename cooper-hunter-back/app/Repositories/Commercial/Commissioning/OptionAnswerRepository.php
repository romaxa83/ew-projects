<?php

namespace App\Repositories\Commercial\Commissioning;

use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class OptionAnswerRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return OptionAnswer::query();
    }
}
