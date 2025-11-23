<?php

namespace App\Http\Requests\Client;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Auth;
use Illuminate\Validation\Rule;

class ProfileSave extends FormRequest
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
            'id' => 'nullable|numeric',
            'name' => 'required|min:1|max:50',
            'lname' => 'nullable|max:80',
            'order_id' => ['nullable', 'integer', Rule::exists(Order::TABLE, 'id')],
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
            'selectedTags.*.key' => 'nullable|numeric',
            'selectedTags.*.value' => 'required|string|max:60',
            'notes.*.id' => 'nullable|numeric',
            'notes.*.user_id' => 'nullable|numeric',
            'notes.*.value' => 'required|string|max:56536',
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

        $input['name'] = strip_tags($input['name']);
        $input['lname'] = strip_tags($input['lname']);

        if (isset($input['phones'])) {
            // Чистим номера от лишнего
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
            // Чистим кривые email
            $input['emails'] = collect($input['emails'])
                ->reject(function ($v) {
                    return !filter_var($v['value'], FILTER_VALIDATE_EMAIL);
                })
                ->all();
        }


        if (isset($input['messengers'])) {
            // Чистим не валидные IM
            $input['messengers'] = $this->sanitizeValue($input['messengers'])->all();
        }

        if (isset($input['notes'])) {
            // Чистим не валидные данные
            $input['notes'] = $this
                ->sanitizeValue($input['notes'])
                ->map(function ($v) {
                    // Проставляем ID того кто создал
                    if (empty($v['id'])) {
                        $v['user_id'] = Auth::id();
                    } else {
                        unset($v['user_id']);
                    }

                    return $v;
                })
                ->all();
        }

        if (isset($input['selectedTags'])) {
            // Чистим не валидные данные
            $input['selectedTags'] = $this->sanitizeValue($input['selectedTags'])->all();
        }

        $this->replace($input);
    }

    /**
     * Remove empty values.
     * @param $data
     * @return array
     */
    private function sanitizeValue($data)
    {
        return collect($data)
            ->reject(function ($v) {
                return empty($v['value']);
            });
    }
}
