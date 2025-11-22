<?php

namespace App\Http\Requests\BodyShop\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UserFilterRequest
 *
 * @property null|string order_by
 * @property null|string order_type
 * @property null|string per_page
 *
 * @package App\Http\Requests\BodyShop\Users
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
            'per_page' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', $this->statusIn()],
            'name' => ['nullable', 'string'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
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
        return ['full_name', 'status', 'email'];
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
