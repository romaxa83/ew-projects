<?php

namespace App\Rules\Import;

use App\Services\Import\Parser\IosLinkParser;
use Illuminate\Contracts\Validation\Rule;

class FileIosLinkRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return count(IosLinkParser::validate(request()->file('file'))) === 0;
    }

    public function message(): string
    {
        return 'Invalid file data';
    }
}
