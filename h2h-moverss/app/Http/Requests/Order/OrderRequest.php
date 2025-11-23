<?php

namespace App\Http\Requests\Order;

use App\Enums\Catalog\MoveSizeTypeEnum;
use App\Http\Requests\JsonRequest;

class OrderRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'division_id' => 'required|numeric|exists:divisions,id',
            'source_id' => 'nullable|numeric|exists:orders_sources,id',
            'move_size_id' => ['nullable', 'numeric', MoveSizeTypeEnum::ruleIn()],
            'type' => 'nullable|in:house,apartment,storage,business',
            'selectedTags.*.id' => 'nullable|numeric',
            'selectedTags.*.title' => 'required|string|max:60',
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

        $input['user_id'] = empty($input['user_id']) ? null : (int) $input['user_id'];
        $input['source_id'] = empty($input['source_id']) ? null : (int) $input['source_id'];
        $input['move_size_id'] = empty($input['move_size_id']) ? null : (int) $input['move_size_id'];

        if (isset($input['selectedTags'])) {
            // Чистим не валидные данные
            $input['selectedTags'] = collect($input['selectedTags'])
                ->reject(function ($v) {
                    return empty($v['title']);
                })->all();
        }

        $this->replace($input);
    }
}
