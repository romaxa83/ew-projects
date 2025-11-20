<?php

namespace WezomCms\Core\Foundation\Notifications;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Session\Store;

class FlashNotification implements Arrayable
{
    /**
     * @var Store
     */
    protected $session;

    /**
     * @var NotifyDriverInterface
     */
    private $notifyDriver;

    /**
     * @var array
     */
    protected $notifications = [];

    /**
     * FlashNotification constructor.
     *
     * @param  Store  $session
     * @param  NotifyDriverInterface  $notifyDriver
     */
    public function __construct(Store $session, NotifyDriverInterface $notifyDriver)
    {
        $this->session = $session;
        $this->notifyDriver = $notifyDriver;
    }

    /**
     * @param  NotifyDriverInterface  $notification
     * @return FlashNotification
     */
    public function push(NotifyDriverInterface $notification): FlashNotification
    {
        $this->notifications[] = $notification;

        $this->flash();

        return $this;
    }

    /**
     * Generate and save success message.
     *
     * @param $title
     * @param  int  $time
     * @return FlashNotification
     */
    public function success($title, $time = 5): FlashNotification
    {
        return $this->push($this->notifyDriver->success($title, $time));
    }

    /**
     * Generate and save info message.
     *
     * @param $title
     * @param  int  $time
     * @return FlashNotification
     */
    public function info($title, $time = 5): FlashNotification
    {
        return $this->push($this->notifyDriver->info($title, $time));
    }

    /**
     * Generate and save warning message.
     *
     * @param $title
     * @param  int  $time
     * @return FlashNotification
     */
    public function warning($title, $time = 5): FlashNotification
    {
        return $this->push($this->notifyDriver->warning($title, $time));
    }

    /**
     * Generate and save error message.
     *
     * @param $title
     * @param  int  $time
     * @return FlashNotification
     */
    public function error($title, $time = 5): FlashNotification
    {
        return $this->push($this->notifyDriver->error($title, $time));
    }

    /**
     * @return FlashNotification
     */
    public function clear(): FlashNotification
    {
        $this->notifications = [];

        $this->flash();

        return $this;
    }

    /**
     * @return array
     */
    public function getNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $notifications = $this->notifications;
        foreach ($notifications as &$notification) {
            $notification = $notification->toArray();
        }

        return $notifications;
    }

    /**
     * Flash all messages to the session.
     */
    protected function flash()
    {
        $this->session->flash('flash-notifications', $this->toArray());
    }
}
