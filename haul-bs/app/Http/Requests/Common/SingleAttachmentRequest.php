<?php

namespace App\Http\Requests\Common;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @property mixed attachment
 */
class SingleAttachmentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'attachment' => $this->fileRule(),
        ];
    }
}
