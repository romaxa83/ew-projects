<?php


namespace App\Http\Requests\Carrier;

use App\Http\Controllers\Api\Carrier\CarrierController;
use App\Models\Saas\Company\CompanyInsuranceInfo;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class UploadInsurancePhotoRequest extends FormRequest
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
            CompanyInsuranceInfo::INSURANCE_FIELD_CARRIER => [
                'required',
                'mimes:' . CarrierController::ALLOWED_FILE_TYPES,
                "max:" . byte_to_kb(CarrierController::MAX_FILE_SIZE)
            ],
        ];
    }
}
