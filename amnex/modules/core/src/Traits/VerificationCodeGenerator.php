<?php

declare(strict_types=1);

namespace Wezom\Core\Traits;

use Exception;

trait VerificationCodeGenerator
{
    /**
     * @throws Exception
     */
    protected function generateVerificationCode(int $length = 6): string
    {
        return (string)random_int(10 ** ($length - 1), 10 ** ($length) - 1);
    }
}
