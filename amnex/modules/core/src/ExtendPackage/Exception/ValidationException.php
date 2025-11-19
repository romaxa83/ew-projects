<?php

declare(strict_types=1);

namespace Wezom\Core\ExtendPackage\Exception;

use Illuminate\Support\Str;

class ValidationException extends \Nuwave\Lighthouse\Exceptions\ValidationException
{
    public const KEY = 'validation';

    /** @return array{validation: array<string, array<int, string>>} */
    public function getExtensions(): array
    {
        $data = [];
        foreach ($this->validator->errors()->messages() as $key => $message) {
            $data[Str::camel($key)] = $message;
        }

        return [
            self::KEY => $data,
        ];
    }
}
