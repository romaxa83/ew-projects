<?php

namespace App\Http\Requests\BodyShop\Settings;

use App\Models\BodyShop\Settings\Settings;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class UploadInfoPhotoRequest extends FormRequest
{
    use OnlyValidateForm;
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            Settings::LOGO_FIELD => [
                'required',
                'image',
                "max:" . byte_to_kb(config('medialibrary.max_file_size'))
            ],
        ];
    }
}
