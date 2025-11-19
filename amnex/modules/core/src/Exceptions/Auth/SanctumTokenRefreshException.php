<?php

declare(strict_types=1);

namespace Wezom\Core\Exceptions\Auth;

use Wezom\Core\Exceptions\ApplicationException;

class SanctumTokenRefreshException extends ApplicationException
{
    protected bool $clientSafe = true;
    protected bool $shouldBeReported = false;
}
