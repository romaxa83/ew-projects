<?php

namespace App\Http\Requests\Common;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @property mixed image
 */
class SingleImageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'image' => $this->imageRule(),
        ];
    }
}
