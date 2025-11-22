<?php

namespace App\Filters\Commercial\Commissioning;

use App\Filters\BaseModelFilter;
use App\Traits\Filter\IdFilterTrait;

class QuestionFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function answerType(string $value): void
    {
        $this->where('answer_type', $value);
    }

    public function protocol($value): void
    {
        $this->where('protocol_id', $value);
    }
}

