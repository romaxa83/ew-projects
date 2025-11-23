<?php

namespace WezomCms\Imports\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class ImportRequest extends FormRequest
{
    use LocalizedRequestTrait;
    use RequiredIfMessageTrait;

    public function rules(): array
    {
        return $this->localizeRules(
            [],
            [
                'file' => ['required', 'file', 'min:3', 'max:15000'],
            ]
        );
    }

    public function attributes(): array
    {
        return $this->localizeAttributes(
            [],
            [
                'file' => __('cms-reports::admin.File'),
            ]
        );
    }
}

