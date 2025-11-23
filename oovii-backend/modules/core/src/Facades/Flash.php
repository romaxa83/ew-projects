<?php

namespace WezomCms\Core\Facades;

use Illuminate\Support\Facades\Facade;
use WezomCms\Core\Foundation\Notifications\FlashNotification;
use WezomCms\Core\Foundation\Notifications\NotifyDriverInterface;

/**
 * Class Flash
 * @package WezomCms\Core\Facades
 * @method static FlashNotification push(NotifyDriverInterface $notification)
 * @method static FlashNotification success($title, $time = 5)
 * @method static FlashNotification info($title, $time = 5)
 * @method static FlashNotification warning($title, $time = 5)
 * @method static FlashNotification error($title, $time = 5)
 * @method static FlashNotification clear()
 * @method static array getNotifications()
 * @method static array toArray()
 */
class Flash extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'flash-notification';
    }
}
