<?php

namespace App\Http\Requests\Common;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @property string search
 */
class SearchRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return $this->searchRule();
    }
}
