<?php

namespace App\Rules\Utils;

use Illuminate\Contracts\Validation\Rule;

class VideoRule implements Rule
{
    protected array $mimes = [
        'video/mp4',
        'video/webm',
    ];

    protected string $attribute;
    protected string $type;

    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $this->type = $value->getMimeType();

        return in_array($this->type, $this->mimes, true);
    }

    public function message(): string
    {
        return __(
            'validation.mimes_with_specified',
            [
                'attribute' => $this->attribute,
                'values' => implode(', ', $this->mimes),
                'type' => $this->type,
            ]
        );
    }
}