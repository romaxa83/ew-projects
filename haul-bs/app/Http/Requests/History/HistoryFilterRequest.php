<?php

namespace App\Http\Requests\History;

use App\Foundations\Http\Requests\BaseFormRequest;

class HistoryFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            [
                'dates_range' => ['nullable', 'string'],
                'user_id' => ['nullable', 'integer'],
            ]
        );
    }
}
