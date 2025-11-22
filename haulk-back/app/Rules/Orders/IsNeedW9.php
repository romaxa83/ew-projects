<?php

namespace App\Rules\Orders;

use App\Http\Requests\Orders\SendDocsRequest;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Models\Media;

class IsNeedW9 implements Rule
{
    private SendDocsRequest $request;

    /**
     * Create a new rule instance.
     * @param SendDocsRequest $request
     * @return void
     */
    public function __construct(SendDocsRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  Media|null  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (empty($this->request->content) || !in_array('w9', $this->request->content)) {
            return true;
        }
        return $value instanceof Media && Storage::exists($value->getPath());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('Missing W9 certificate.');
    }
}
