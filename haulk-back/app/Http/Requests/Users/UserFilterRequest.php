<?php

namespace App\Http\Requests\Users;

use App\Models\Tags\Tag;
use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UserFilterRequest
 *
 * @property null|string order_by
 * @property null|string order_type
 * @property null|string per_page
 *
 * @package App\Http\Requests\Users
 */
class UserFilterRequest extends FormRequest
{
    const PER_PAGE = 10;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $this->setDefaults();

        return [
            'order_by' => ['nullable', 'string', $this->orderByIn()],
            'order_type' => ['nullable', 'string', $this->orderTypeIn()],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'per_page' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', $this->statusIn()],
            'name' => ['nullable', 'string'],
            'my_drivers' => ['nullable', 'boolean'],
            'tag_id' => [
                'nullable',
                'integer',
                Rule::exists(Tag::class, 'id')
                    ->where('type', Tag::TYPE_VEHICLE_OWNER),
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'my_drivers' => $this->boolean('my_drivers'),
        ]);
    }

    protected function setDefaults(): void
    {
        if (is_null($this->order_by)) {
            $this->order_by = 'status';
        }

        if (is_null($this->order_type)) {
            $this->order_type = 'desc';
        }

        if (is_null($this->per_page)) {
            $this->per_page = self::PER_PAGE;
        }
    }

    public function sortableAttributes(): array
    {
        return ['id', 'full_name', 'status', 'email', 'last_login'];
    }

    public function filterableAttributes(): array
    {
        return ['role_id', 'status', 'name', 'my_drivers', 'tag_id'];
    }

    public function validatedForFilter(): array
    {
        $fields = $this->filterableAttributes();

        if ($this->input('my_drivers')) {
            $fields = array_diff($fields, ['role_id',]);
        }

        return $this->only($fields);
    }

    protected function orderTypeIn(): string
    {
        return 'in:' . implode(',', ['asc', 'desc']);
    }

    protected function orderByIn(): string
    {
        return 'in:' . implode(',', $this->sortableAttributes());
    }

    protected function statusIn(): string
    {
        return 'in:' . implode(',', [User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_PENDING]);
    }
}
