<?php

namespace App\Http\Requests\Reports;

use App\Models\Orders\Payment;
use App\Models\Reports\DriverTripReport;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverTripReportRequest extends FormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if (!$user || !$user->isDriver()) {
                        $fail(trans('Driver not found.'));
                    }
                }
            ],
            'report_date' => ['required', 'date_format:m/d/Y'],
            'date_to' => ['required', 'date_format:m/d/Y'],
            'date_from' => ['required', 'date_format:m/d/Y'],
            DriverTripReport::DRIVER_FILE_FIELD_NAME => ['nullable', 'file'],

        ];
    }

}
