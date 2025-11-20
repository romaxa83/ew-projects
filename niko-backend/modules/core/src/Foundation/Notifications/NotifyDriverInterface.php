<?php

namespace WezomCms\Core\Foundation\Notifications;

use Illuminate\Contracts\Support\Arrayable;

interface NotifyDriverInterface extends Arrayable
{
    /**
     * @return NotifyDriverInterface|mixed
     */
    public function make();

    /**
     * Generate success message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function success($title, $time = 5): NotifyDriverInterface;

    /**
     * Generate info message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function info($title, $time = 5): NotifyDriverInterface;

    /**
     * Generate warning message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function warning($title, $time = 5): NotifyDriverInterface;

    /**
     * Generate error message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function error($title, $time = 5): NotifyDriverInterface;

    /**
     * @param $title
     * @return NotifyDriverInterface
     */
    public function title($title): NotifyDriverInterface;

    /**
     * @param $text
     * @return NotifyDriverInterface
     */
    public function text($text): NotifyDriverInterface;

    /**
     * @param $type
     * @return NotifyDriverInterface
     */
    public function type($type): NotifyDriverInterface;

    /**
     * @param $time
     * @return NotifyDriverInterface
     */
    public function time($time): NotifyDriverInterface;

    /**
     * @param  bool  $flag
     * @return NotifyDriverInterface
     */
    public function asToast($flag = true): NotifyDriverInterface;

    /**
     * @param $key
     * @param $value
     * @return NotifyDriverInterface
     */
    public function set($key, $value): NotifyDriverInterface;

    /**
     * Adding this notify to the flash container.
     *
     * @return NotifyDriverInterface
     */
    public function flash(): NotifyDriverInterface;
}
