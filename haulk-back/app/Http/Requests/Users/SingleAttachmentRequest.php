<?php

namespace App\Http\Requests\Users;

use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed attachment
 */
class SingleAttachmentRequest extends FormRequest
{
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attachment' => [
                'required',
                'file',
                $this->orderAttachmentTypes()
            ],
        ];
    }
}
