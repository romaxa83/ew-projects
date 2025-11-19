<?php

namespace Wezom\Core\Testing;

use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use LogicException;
use PHPUnit\Framework\Assert;

/**
 * @mixin Assert
 */
trait QueryInterceptor
{
    protected function startQueryCount(): void
    {
        $this->startQueryLog();
    }

    protected function startQueryLog(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
    }

    protected function assertQueryCount(int $count, string $message = ''): void
    {
        if (!DB::logging()) {
            throw new LogicException('Query logging must be started in order to count queries!');
        }

        static::assertThat($count, new QueryCount($count), $message);
    }

    #[NoReturn]
    protected function showQueryLog(): void
    {
        $message = (new QueryCount(0))->showQueryLog();

        static::fail($message);
    }
}
