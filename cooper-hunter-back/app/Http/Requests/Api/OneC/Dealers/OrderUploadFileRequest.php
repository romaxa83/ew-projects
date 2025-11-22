<?php

namespace App\Http\Requests\Api\OneC\Dealers;

use App\Http\Requests\BaseFormRequest;

class OrderUploadFileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'files' => ['array', 'required'],
            'files.*.file' => ['required'],
            'files.*.name' => ['required'],
            'files.*.extension' => ['required'],
        ];
    }
}


