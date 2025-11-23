<?php

namespace App\Http\Requests\Order;

use App\Models\Order\Work;
use Illuminate\Foundation\Http\FormRequest;

class WorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:orders_works,id',
            'order_id' => 'required|integer|exists:orders,id',
            'start_date' => 'nullable|date_format:"Y-m-d"',
            'start_time' => 'nullable|date_format:"H:i:s"',
            'start_time_to' => 'nullable|date_format:"H:i:s"|after:start_time',
            'notes' => 'nullable|string|max:65536',
            'duration' => 'nullable|numeric|min:1|max:999',
            'trucks' => 'nullable|integer|max:10',
            'employees' => 'required|integer|min:1|max:20',
            'work_types_checked' => 'required|array',
            'work_types_checked.*' => 'nullable|exists:works_types,id', // Чекаем массив
        ];
    }

    public function attributes(): array
    {
        return [
            'in_dispatch' => '`Display in Dispatch`',
            'trucks' => '`Trucks qty`',
            'employees' => '`Crew qty`',
            'work_types_checked' => '`Service Types`',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ((
                    in_array(1, $this->work_types_checked, true) || // Moving
                    in_array(3, $this->work_types_checked, true) || // Loading
                    in_array(4, $this->work_types_checked, true) // Unloading
                ) && $this->trucks === null) {
                $validator->errors()->add('trucks', 'Moving without tracks is impossible');
            }

            if ($this->id) {
                $record = Work::withCount(['dispatchTrucks', 'dispatchEmployees'])->find($this->id);

                if ($record->dispatch_employees_count || $record->dispatch_trucks_count) {
                    if ($record->start_date != $this->start_date) {
                        $validator->errors()->add('start_date',
                            'The service has assignments. You cannot change the date of services');
                    }
                    if ($record->dispatch_trucks_count > $this->trucks) {
                        $validator->errors()->add('trucks',
                            'You cannot decrease trucks qty! The service already has dispatch assignment to '.$record->dispatch_trucks_count.' trucks!');
                    }
                    if ($record->dispatch_employees_count > $this->employees) {
                        $validator->errors()->add('employees',
                            'You cannot decrease employees qty! The service already has dispatch assignment to '.$record->dispatch_employees_count.' employees!');
                    }
                }
            }


            if ($this->in_dispatch) {
                if (empty($this->start_date)) {
                    $validator->errors()->add('start_date', 'You must select Date');
                }
                if (empty($this->start_time)) {
                    $validator->errors()->add('start_time', 'You must fill Start Time');
                }
                if (empty($this->duration)) {
                    $validator->errors()->add('duration', 'You must fill Duration');
                }
                if (empty($this->employees)) {
                    $validator->errors()->add('employees', 'You must select Crews Size');
                }
            }
        });
    }
}
