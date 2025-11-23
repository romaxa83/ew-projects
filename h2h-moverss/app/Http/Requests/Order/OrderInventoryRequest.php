<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\JsonRequest;

class OrderInventoryRequest extends JsonRequest
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
            'order_id' => 'required|integer|exists:orders,id',
            'records.*.id' => 'nullable|numeric',
            'records.*.randomRef' => 'nullable|string|max:32',
            'records.*.is_section' => 'in:1,0',
            'records.*.price' => 'nullable|numeric',
            'records.*.qty' => 'nullable|integer',
            'records.*.weight' => 'nullable|numeric',
            'records.*.volume' => 'nullable|numeric',
            'records.*.title' => 'nullable|string|max:95',
            'records.*.sort' => 'required|integer',
            'records.*.item_id' => 'nullable|integer',
            'records.*.children.*.id' => 'nullable|numeric',
            'records.*.children.*.randomRef' => 'nullable|string|max:32',
            'records.*.children.*.is_section' => 'in:0',
            'records.*.children.*.price' => 'nullable|numeric',
            'records.*.children.*.qty' => 'nullable|integer',
            'records.*.children.*.weight' => 'nullable|numeric',
            'records.*.children.*.volume' => 'nullable|numeric',
            'records.*.children.*.title' => 'nullable|string|max:95',
            'records.*.children.*.sort' => 'required|integer',
            'records.*.children.*.item_id' => 'nullable|integer',
        ];
    }

    public function attributes(): array
    {
        return [
            'records.*.title' => 'Title',
            'records.*.children.*.title' => 'Item title',
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

        if (isset($input['records'])) {
            $input['records'] = $this->sanitizeValue($input['records']);
        }

        $this->replace($input);
    }

    private function sanitizeValue($values): array
    {
        return collect($values)
            ->map(function ($v) {
                $v['id'] = !empty($v['id']) ? (int) $v['id'] : null;

                $v['title'] = !empty($v['title']) ? strip_tags($v['title']) : null;
                $v['price'] = !empty($v['price']) && $v['price'] > 0 ? (float) $v['price'] : null;
                $v['qty'] = !empty($v['qty']) && $v['qty'] > 0 ? (int) $v['qty'] : null;
                $v['item_id'] = isset($v['item_id']) && $v['item_id'] > 0 ? (int) $v['item_id'] : null;
                $v['weight'] = !empty($v['weight']) && $v['weight'] > 0 ? (float) $v['weight'] : null;
                $v['volume'] = !empty($v['volume']) && $v['volume'] > 0 ? (float) $v['volume'] : null;
                $v['is_section'] = (int) $v['is_section'];
                $v['sort'] = (int) $v['sort'];

                if (!$v['title'] && ($v['price'] || $v['weight'] || $v['volume'])) {
                    $v['title'] = 'Not filled title';
                }

                if (isset($v['children'])) {
                    $v['children'] = $this->sanitizeValue($v['children']);

                    if (!$v['title'] && count($v['children'])) {
                        $v['title'] = 'No name room';
                    }
                }

                return $v;
            })
            ->filter(function ($v) {
                // Filter Empty Item
                if (!$v['is_section'] && !$v['title'] && !$v['price'] && !$v['weight'] && !$v['volume']) {
                    return 0;
                }

                // Filter Empty Room
                if ($v['is_section'] && !$v['title'] && !count($v['children'])) {
                    return 0;
                }

                return 1;
            })
            ->all();
    }
}
