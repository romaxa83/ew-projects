<?php

namespace App\Http\Requests\Vehicles;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int $per_page
 */
abstract class VehicleIndexRequest extends FormRequest
{
    protected const PER_PAGE = 10;

    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->setDefaults();

        return [
            'q' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer', Rule::exists(User::TABLE_NAME, 'id')],
            'tag_id' => [
                'nullable',
                'integer',
                Rule::exists(Tag::class, 'id')
                    ->where('type', Tag::TYPE_TRUCKS_AND_TRAILER),
            ],
            'order_by' => ['nullable', 'string', $this->orderByIn()],
            'order_type' => ['nullable', 'string', $this->orderTypeIn()],
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }

    abstract protected function sortableAttributes(): array;

    protected function orderByIn(): string
    {
        return 'in:' . implode(',', $this->sortableAttributes());
    }

    protected function orderTypeIn(): string
    {
        return 'in:' . implode(',', ['asc', 'desc']);
    }
}
