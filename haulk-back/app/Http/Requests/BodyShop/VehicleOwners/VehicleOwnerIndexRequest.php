<?php

namespace App\Http\Requests\BodyShop\VehicleOwners;

use App\Models\Tags\Tag;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleOwnerIndexRequest extends FormRequest
{
    private const PER_PAGE = 10;

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('vehicle-owners');
    }

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
            'tag_id' => [
                'nullable',
                'integer',
                Rule::exists(Tag::class, 'id')
                    ->where('type', Tag::TYPE_VEHICLE_OWNER),
            ]
        ];
    }

    protected function setDefaults(): void
    {
        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }
}
