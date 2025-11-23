<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\JsonRequest;

class OrderMaterialsRequest extends JsonRequest
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
            'order_id' => 'required|integer|exists:orders,id',
            'records' => 'nullable|array',
            'custom_records' => 'nullable|array',
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

        if (isset($input['records'])) {
            $input['records'] = $this->sanitizeRecords($input['records'], $input['order_id']);
        }
        if (isset($input['custom_records'])) {
            $input['custom_records'] = collect($input['custom_records'])
                ->map(function ($v) {
                    return [
                        'id' => $v['id'] ?? null,
                        'title' => strip_tags($v['title']),
                        'price' => (float) $v['price'],
                    ];
                })
                ->all();
        }

        $this->replace($input);
    }

    private function sanitizeRecords($values, $order_id)
    {
        return collect($values)
            ->map(function ($v) use ($order_id) {
                return [
                    'material_id' => $v['id'],
                    'order_id' => $order_id,
                    'checked' => $v['checked']['checked'],
                    'type_id' => (int) $v['group_id'],
                    'group_id' => (int) $v['group_id'],
                    'qty' => (int) $v['qty'],
                    'title' => strip_tags($v['title']),
                    'price' => (float) $v['price'],
                    'need_packing' => $v['checked']['packing_checked'] ? 1 : 0,
                    'need_unpacking' => $v['checked']['unpacking_checked'] ? 1 : 0,
                    'packing_price' => $v['packing_price'] > 0 ? (float) $v['packing_price'] : null,
                    'unpacking_price' => $v['unpacking_price'] > 0 ? (float) $v['unpacking_price'] : null,
                ];
            })
            ->filter(function ($item) {
                return $item['checked'] && $item['qty'];
            })
            ->all();
    }
}
