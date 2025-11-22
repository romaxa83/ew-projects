<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class ImageRule implements Rule
{
    /**
     * @param string $attribute
     * @param UploadedFile $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $extension = strtolower($value->getClientOriginalExtension());

        return in_array($extension, ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'tiff']);
    }

    public function message(): string
    {
        return 'The file must be a file of type: jpeg, jpg, png, gif, bmp, tiff.';
    }
}

