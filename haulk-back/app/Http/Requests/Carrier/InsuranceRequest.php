<?php


namespace App\Http\Requests\Carrier;


use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

class InsuranceRequest extends FormRequest
{
    use OnlyValidateForm, ValidationRulesTrait;
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
            'insurance_expiration_date' => ['required', 'string', 'max:191'],
            'insurance_cargo_limit' => ['required', 'integer'],
            'insurance_deductible' => ['required', 'integer'],
            'insurance_agent_name' => ['required', 'string', 'max:255'],
            'insurance_agent_phone' => ['required', $this->USAPhone(), 'string', 'max:191'],
        ];
    }

}
