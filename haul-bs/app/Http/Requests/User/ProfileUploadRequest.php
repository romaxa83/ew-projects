<?php

namespace App\Http\Requests\User;

use App\Foundations\Http\Requests\BaseFormRequest;
use App\Models\Users\User;

class ProfileUploadRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            User::MEDIA_COLLECTION_NAME => ['required', 'image',
                "max:" . byte_to_kb(config('media-library.max_file_size'))
            ],
        ];
    }
}
