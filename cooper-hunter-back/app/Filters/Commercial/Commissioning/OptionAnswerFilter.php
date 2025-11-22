<?php

namespace App\Filters\Commercial\Commissioning;

use App\Filters\BaseModelFilter;
use App\Traits\Filter\IdFilterTrait;

class OptionAnswerFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function question($value): void
    {
        $this->where('question_id', $value);
    }
}

