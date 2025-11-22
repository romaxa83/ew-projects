<?php

namespace App\Http\Requests\Payrolls;

use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class PayrollRequest extends PreparePayrollRequest
{
    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return parent::rules() + [
            'start' => ['required', 'date_format:m/d/Y'],
            'end' => ['required', 'date_format:m/d/Y'],
            'notes' => ['nullable', 'string'],
            'driver_rate' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'subtotal' => ['required', 'numeric'],
            'commission' => ['required', 'numeric'],
            'salary' => ['required', 'numeric'],
            'expenses_before' => ['nullable', 'array'],
            'expenses_before.*.type' => ['required', 'string'],
            'expenses_before.*.price' => ['required', 'numeric'],
            'expenses_after' => ['nullable', 'array'],
            'expenses_after.*.type' => ['required', 'string'],
            'expenses_after.*.price' => ['required', 'numeric'],
            'expenses_after.*.note' => ['nullable', 'string', 'min:2', 'max:1000'],
            'bonuses' => ['nullable', 'array'],
            'bonuses.*.type' => ['required', 'string'],
            'bonuses.*.price' => ['required', 'numeric'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        if ($this->has(['start', 'end'])) {
            $validator->after(
                function ($validator) {
                    $start = new Carbon($this->input('start'));
                    $end = new Carbon($this->input('end'));

                    if ($end->diffInHours($start, false) >= 0) {
                        $validator
                            ->errors()
                            ->add('start', trans('Please select correct dates.'))
                            ->add('end', trans('Please select correct dates.'));
                    }
                }
            );
        }
    }
}
