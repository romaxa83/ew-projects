<?php

namespace App\Http\Requests\Company;

use App\Enums\Employee\SalesTeamEnum;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Auth;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:employees,id',
            'name' => 'required|string|max:50',
            'l_name' => 'nullable|string|max:70',
            'address' => 'nullable|string|max:250',
            'birthday' => 'nullable|date_format:"Y-m-d"',
            'pay_type' => 'nullable|in:hour,month',
            'signature' => 'nullable|string|max:56536',
            'pbx_ext' => 'nullable|numeric',
            'pbx_show_webrtc' => 'numeric',
            'phones.*.id' => 'nullable|numeric',
            'phones.*.sort' => 'nullable|numeric',
            'phones.*.is_primary' => 'numeric',
            'phones.*.type_id' => 'numeric',
            'phones.*.value' => 'required|string',
            'emails.*.id' => 'nullable|numeric',
            'emails.*.sort' => 'nullable|numeric',
            'emails.*.is_primary' => 'numeric',
            'emails.*.value' => 'required|string|max:60',
            'messengers.*.id' => 'nullable|numeric',
            'messengers.*.type_id' => 'numeric',
            'messengers.*.value' => 'required|string|max:60',
            'notes.*.id' => 'nullable|numeric',
            'notes.*.user_id' => 'nullable|numeric',
            'notes.*.value' => 'required|string|max:56536',
            'busy_dates' => 'nullable|array',
            'busy_weeks_days.miscs' => 'nullable|array|in:0,1,2,3,4,5,6',
            'pbx_data' => 'nullable|array',
            'pbx_data.*.pbx_ext' => 'nullable|numeric',
            'pbx_data.*.pbx_password' => 'nullable|string|max:100',
            'pbx_data.*.pbx_show_webrtc' => 'nullable|numeric',
            'pbx_data.*.pbx_id' => 'nullable|numeric',
            'pbx_data.*.id' => 'nullable|numeric',
            'roles' => 'required|array|min:1',
            'roles.*' => 'nullable|exists:users_roles,id',
            'active' => 'nullable|in:0,1',
            'user.active' => 'required|in:0,1',
            'send_welcome' => 'boolean',
            'division_ids' => 'required|array|exists:divisions,id',
            'driver_start_of_work' => 'nullable|date_format:"Y-m-d"',
            'driver_notes' => 'nullable|string|max:56536',
            'partner_id' => ['nullable', 'integer', Rule::exists(Partner::TABLE, 'id')],
            'sales_team' => ['nullable', 'string', SalesTeamEnum::ruleIn()],
        ];
    }

    protected function prepareForValidation()
    {
        $this->sanitizeInput();

        return $this->getValidatorInstance();
    }

    public function attributes(): array
    {
        return [
            'roles' => 'Job Roles',
        ];
    }

    private function sanitizeInput(): void
    {
        $input = $this->all();

        $input['active'] = !empty($input['active']) ? 1 : 0;
        $input['user']['active'] = !empty($input['user']['active']) ? 1 : 0;

        $input['name'] = strip_tags($input['name']);
        $input['l_name'] = strip_tags($input['l_name']);
        $input['address'] = strip_tags($input['address']);
        $input['signature'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $input['signature']);


        if (isset($input['phones'])) {
            // Clear phones
            $input['phones'] = collect($input['phones'])
                ->map(function ($v) {
                    $v['value'] = preg_replace('/[^0-9]/', '', $v['value']);
                    $v['value'] = (strlen($v['value']) >= 7) ? $v['value'] : null;

                    return $v;
                })
                ->reject(function ($v) {
                    return empty($v['value']);
                })
                ->all();
        }

        if (isset($input['emails'])) {
            // Remove invalid emails
            $input['emails'] = collect($input['emails'])
                ->reject(function ($v) {
                    return !filter_var($v['value'], FILTER_VALIDATE_EMAIL);
                })
                ->all();
        }

        if (isset($input['messengers'])) {
            // Format IMs
            $input['messengers'] = $this->sanitizeValue($input['messengers'])->all();
        }

        if (isset($input['notes'])) {
            // Format notes
            $input['notes'] = $this
                ->sanitizeValue($input['notes'])
                ->map(function ($v) {
                    // Set holder ID
                    if (empty($v['id'])) {
                        $v['user_id'] = Auth::id();
                    } else {
                        unset($v['user_id']);
                    }

                    return $v;
                })
                ->all();
        }

        if (isset($input['busy_dates'])) {
            $input['busy_dates'] = $this->formatBusyDates($input['busy_dates']);
        }

        $this->replace($input);
    }

    /**
     * Remove scripts + remove empty.
     * @param $data
     * @return Collection
     */
    private function sanitizeValue($data): Collection
    {
        return collect($data)
            ->map(function ($v) {
                $v['value'] = strip_tags($v['value']);

                return $v;
            })
            ->reject(function ($v) {
                return empty($v['value']);
            });
    }

    /**
     * Format data for saving.
     * @param $records
     * @return array
     */
    private function formatBusyDates($records): array
    {
        return collect($records)
            ->map(function ($v) {
                $startDate = substr($v['startDate'], 0, 10).' '.$v['startTime'];
                $startDate = date('Y-m-d H:i:s', strtotime($startDate));

                $endDate = substr($v['endDate'], 0, 10).' '.$v['endTime'];
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
