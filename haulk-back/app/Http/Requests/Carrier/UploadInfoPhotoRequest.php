<?php


namespace App\Http\Requests\Carrier;

use App\Http\Controllers\Api\Carrier\CarrierController;
use App\Models\Saas\Company\Company;
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
            Company::LOGO_FIELD_CARRIER => [
                'required',
                'mimes:' . CarrierController::ALLOWED_FILE_TYPES,
                "max:" . byte_to_kb(CarrierController::MAX_FILE_SIZE)
            ],
        ];
    }
}
