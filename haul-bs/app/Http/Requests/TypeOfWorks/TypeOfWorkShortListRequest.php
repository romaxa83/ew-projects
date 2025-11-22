<?php

namespace App\Http\Requests\TypeOfWorks;

use App\Foundations\Http\Requests\Common\SearchRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

class TypeOfWorkShortListRequest extends SearchRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return parent::rules();
    }
}
