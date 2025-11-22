<?php

namespace Core\Facades;

use Closure;
use Core\Sms\SmsManager;
use Core\Sms\SmsSender;
use Core\Testing\Fake\SmsFake;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @see SmsSender::to()
 * @method SmsSender to(string|Model $phone)
 *
 * @see SmsFake::assertSent()
 * @method void assertSent(string $smsable, ?Closure $callback = null)
 *
 * @see SmsFake::assertQueued()
 * @method void assertQueued(string $smsable, ?Closure $callback = null)
 */
class Sms extends Facade
{
    public static function fake(): SmsFake
    {
        static::swap($fake = new SmsFake());

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return SmsManager::class;
    }
}
