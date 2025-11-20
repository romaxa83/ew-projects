<?php

namespace WezomCms\Core\Foundation\Notifications\Drivers;

use Flash;
use WezomCms\Core\Foundation\Notifications\NotifyDriverInterface;

class SwalDriver implements NotifyDriverInterface
{
    protected $data;

    /**
     * @return NotifyDriverInterface
     */
    public function make()
    {
        return new static();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Generate success message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function success($title, $time = 5): NotifyDriverInterface
    {
        return $this->simpleToast('success', $title, $time);
    }

    /**
     * Generate info message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function info($title, $time = 5): NotifyDriverInterface
    {
        return $this->simpleToast('info', $title, $time);
    }

    /**
     * Generate warning message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function warning($title, $time = 5): NotifyDriverInterface
    {
        return $this->simpleToast('warning', $title, $time);
    }

    /**
     * Generate error message.
     *
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    public function error($title, $time = 5): NotifyDriverInterface
    {
        return $this->simpleToast('error', $title, $time);
    }

    /**
     * @param $title
     * @return NotifyDriverInterface
     */
    public function title($title): NotifyDriverInterface
    {
        return $this->set('title', $title);
    }

    /**
     * @param $text
     * @return NotifyDriverInterface
     */
    public function text($text): NotifyDriverInterface
    {
        return $this->set('text', $text);
    }

    /**
     * @param $type
     * @return NotifyDriverInterface
     */
    public function type($type): NotifyDriverInterface
    {
        return $this->set('icon', $type);
    }

    /**
     * @param $time
     * @return NotifyDriverInterface
     */
    public function time($time): NotifyDriverInterface
    {
        return $this->set('time', $time * 1000);
    }

    /**
     * @param  bool  $flag
     * @return NotifyDriverInterface
     */
    public function asToast($flag = true): NotifyDriverInterface
    {
        if (empty($this->data['position'])) {
            $this->set('position', config('cms.core.main.notification.default_toast_position', 'center'));
        }

        if (empty($this->data['showConfirmButton'])) {
            $this->set('showConfirmButton', false);
        }

        return $this->set('toast', $flag);
    }

    /**
     * @param $key
     * @param $value
     * @return NotifyDriverInterface
     */
    public function set($key, $value): NotifyDriverInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Adding this notify to the flash container.
     *
     * @return NotifyDriverInterface
     */
    public function flash(): NotifyDriverInterface
    {
        Flash::push($this);

        return $this;
    }

    /**
     * @param  string  $type
     * @param $title
     * @param  int  $time  - time in seconds
     * @return NotifyDriverInterface
     */
    private function simpleToast(string $type, $title, int $time): NotifyDriverInterface
    {
        return $this->make()
            ->type($type)
            ->title($title)
            ->time($time)
            ->asToast();
    }
}
