<?php

namespace App\Http\Requests\Common;

use App\Foundations\Http\Requests\BaseFormRequest;

/**
 * @property mixed logo
 */
class UploadLogoRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'logo' => $this->imageRule(),
        ];
    }
}
