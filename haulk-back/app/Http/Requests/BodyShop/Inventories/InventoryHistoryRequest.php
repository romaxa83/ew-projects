<?php

namespace App\Http\Requests\BodyShop\Inventories;

use Illuminate\Foundation\Http\FormRequest;

class InventoryHistoryRequest extends FormRequest
{
    private const PER_PAGE = 10;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->setDefaults();

        return [
            'dates_range' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }
}
