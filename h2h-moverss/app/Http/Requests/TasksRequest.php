<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'record.type_id' => 'nullable|integer|exists:tasks_types,id',
            'record.executor_id' => 'required|integer|exists:users,id',
            'record.title' => 'nullable|string|min:2|max:80',
            'record.description' => 'nullable|string|max:5000',
            'record.priority' => 'nullable|numeric',
            'record.due_date' => 'required|string|date_format:"Y-m-d H:i:s"',
            'record.order_id' => 'nullable|integer',
            'record.division_id' => 'nullable|integer',
            'record.subscribers' => 'nullable|array',
            'record.subscribers.*' => 'required|exists:users,id',
            'record.miscs' => 'nullable',
            'record.miscs.href' => 'nullable|string',
            'record.miscs.relation.type' => 'nullable|in:order,client',
            'record.miscs.relation.id' => 'nullable|integer',
            'record.miscs.relation.branch_id' => 'nullable|integer|exists:divisions,id',
            'record.miscs.relation.client_id' => 'nullable|integer|exists:clients,id',
            'returnFormat' => 'nullable|string',
            'orderID' => 'nullable|exists:orders,id',
//            "record.notify_holder" => null
//            "record.notify_subscribers" => null
        ];
    }

    public function attributes(): array
    {
        return [
            'record.due_date' => 'Due Date',
            'record.executor_id' => 'Executor',
            'record.title' => 'Title',
            'record.description' => 'Description',
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

        $input['record']['title'] = strip_tags($input['record']['title']);
        $input['record']['description'] = strip_tags($input['record']['description'], '<br/>');

        $this->replace($input);
    }
}
