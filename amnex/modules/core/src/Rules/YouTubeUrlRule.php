<?php

namespace Wezom\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YouTubeUrlRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValidYouTubeUrl($value)) {
            $fail(__('core::validation.custom.invalid-youtube-link'));
        }
    }

    protected function isValidYouTubeUrl($url): bool
    {
        $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/';

        return preg_match($pattern, $url) === 1;
    }
}
