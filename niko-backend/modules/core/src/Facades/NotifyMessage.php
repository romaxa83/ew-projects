<?php

namespace WezomCms\Core\Facades;

use Illuminate\Support\Facades\Facade;
use WezomCms\Core\Foundation\Notifications\NotifyDriverInterface;

/**
 * Class NotifyMessage
 * @package WezomCms\Core\Facades
 * @method static NotifyDriverInterface make()
 * @method static NotifyDriverInterface success($title, $time = 5)
 * @method static NotifyDriverInterface info($title, $time = 5)
 * @method static NotifyDriverInterface warning($title, $time = 5)
 * @method static NotifyDriverInterface error($title, $time = 5)
 * @method static NotifyDriverInterface title($title)
 * @method static NotifyDriverInterface text($title)
 * @method static NotifyDriverInterface type($type)
 * @method static NotifyDriverInterface time($time)
 * @method static NotifyDriverInterface asToast($flag = true)
 * @method static NotifyDriverInterface set($key, $value)
 */
class NotifyMessage extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return NotifyDriverInterface::class;
    }
}
