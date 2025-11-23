<?php

namespace App\Http\Requests\Company;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class PeakDateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'records.*.startDate' => 'required|string',
            'records.*.is_virtual' => 'boolean',
            'records.*.id' => 'nullable|integer|exists:peaks_dates,id',
            'records.*.type_id' => 'required|numeric|max:10',
            'records.*.date' => 'nullable|string|max:50',
            'records.*.description' => 'nullable|string|max:50',
            'peakWeekDays' => 'array|max:7',
        ];
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput()
    {
        $input = $this->all();

        $input['records'] = collect($input['records'])
            ->map(function ($v) {
                return [
                    'id' => $v['id'] ?? null,
                    'is_virtual' => $v['is_virtual'] ?? false,
                    'type_id' => $v['type_id'],
                    'startDate' => $v['startDate'],
                    'date' => $v['startDate'],
                    'description' => !empty($v['description']) ? strip_tags($v['description'], '<br/>') : null,
                ];
            })
            ->all();

        $this->replace($input);
    }

}
