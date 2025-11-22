<?php

namespace App\Http\Requests\Api\OneC;

use App\Http\Requests\BaseFormRequest;
use App\Models\Users\User;
use App\Rules\UniqueValuesByFieldRule;
use Illuminate\Validation\Rule;

abstract class AbstractUpdateGuidRequest extends BaseFormRequest
{
    protected const MODEL = User::class;
    protected const PRIMARY_KEY = 'id';

    public function rules(): array
    {
        return [
            'data' => ['required', 'array', 'min:1', new UniqueValuesByFieldRule('guid')],
            'data.*.id' => ['required', 'int', Rule::exists(static::MODEL, static::PRIMARY_KEY)],
            'data.*.guid' => ['required', 'string', 'uuid', Rule::unique(static::MODEL, 'guid')],
        ];
    }
}
