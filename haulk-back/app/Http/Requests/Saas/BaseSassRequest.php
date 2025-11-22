<?php

namespace App\Http\Requests\Saas;

use App\Models\Admins\Admin;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseSassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function user($guard = Admin::GUARD): Admin
    {
        return parent::user($guard);
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'max:' . config('admins.paginate.max_per_page')],
            'order' => ['nullable', $this->orderBy()],
            'order_type' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function orderBy(): string
    {
        return 'in:' . implode(
                ',',
                [
                    'id',
                    'title',
                ]
            );
    }

    public function getPage(): int
    {
        return $this->page ?? 1;
    }

    public function getPerPage(): int
    {
        return $this->per_page ?? config('admins.paginate.per_page');
    }
}
