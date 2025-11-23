<?php

namespace App\Http\Requests\Company;

use App\Models\Partners\Partner;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrucksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:trucks,id',
            'title' => 'nullable|string|max:50',
            'active' => 'nullable|boolean',
            'color' => 'nullable|string|max:50',
            'l_plate' => 'nullable|string|max:20',
            'model' => 'nullable|string|max:50',
            'nickname' => 'nullable|string|max:50',
            'p_color' => 'nullable|string|max:7',
            'vendor' => 'nullable|string|max:25',
            'year' => 'nullable|date_format:"Y"',
            'vin' => 'nullable|string|max:50',
            'notes.*.id' => 'nullable|numeric',
            'notes.*.value' => 'required|string|max:65536',
            'busy_dates' => 'nullable|array',
            'busy_weeks_days.miscs' => 'nullable|array|in:0,1,2,3,4,5,6',
            'division_ids' => 'required|array|exists:divisions,id',
            'partner_id' => ['nullable', 'integer', Rule::exists(Partner::TABLE, 'id')],
        ];
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    private function sanitizeInput(): void
    {
        $input = $this->all();
;
        $input['active'] = !empty($input['active']) ? 1 : 0;

        $input['title'] = strip_tags($input['title'] ?? null);
        $input['color'] = strip_tags($input['color'] ?? null);
        $input['l_plate'] = strip_tags($input['l_plate'] ?? null);
        $input['vendor'] = strip_tags($input['vendor'] ?? null);
        $input['model'] = strip_tags($input['model'] ?? null);
        $input['nickname'] = strip_tags($input['nickname'] ?? null);
        $input['vin'] = strip_tags($input['vin'] ?? null);

        if (isset($input['notes'])) {
            $input['notes'] = $this->sanitizeNotes($input['notes']);
        }

        if (isset($input['busy_dates'])) {
            $input['busy_dates'] = $this->formatBusyDates($input['busy_dates']);
        }

//        dd($input, 'in');

        $this->replace($input);
    }

    private function sanitizeNotes($records): array
    {
        return collect($records)
            ->map(function ($v) {
                $record = [
                    'id' => $v['id'] ?? null,
                    'value' => strip_tags($v['value']),
                ];
                if (empty($v['id'])) {
                    $record['user_id'] = Auth::id();
                } else {
                    unset($record['user_id']);
                }

                return $record;
            })
            ->filter(function ($item) {
                return !empty($item['value']);
            })
            ->all();
    }

    /**
     * Format a data for saving.
     * @param $records
     * @return array
     */
    private function formatBusyDates($records): array
    {
        return collect($records)
            ->map(function ($v) {
                $startDate = substr($v['startDate'], 0, 10) . ' ' . $v['startTime'];
                $startDate = date('Y-m-d H:i:s', strtotime($startDate));

                $endDate = substr($v['endDate'], 0, 10) . ' ' . $v['endTime'];
                $endDate = date('Y-m-d H:i:s', strtotime($endDate));

                $record = [
                    'id' => $v['id'] ?? null,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'reason' => strip_tags($v['name']),
                ];
                if (empty($v['id'])) {
                    $record['user_id'] = Auth::id();
                } else {
                    unset($record['user_id']);
                }

                return $record;
            })
            ->all();
    }

}
