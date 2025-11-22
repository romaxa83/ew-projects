<?php


namespace App\Http\Requests\Users;


use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
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
            resolve(User::class)->getImageField() => [
                'required',
                'image',
                "max:" . byte_to_kb(config('medialibrary.max_file_size'))
            ],
        ];
    }
}
