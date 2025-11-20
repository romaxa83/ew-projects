<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use WezomCms\Core\Facades\NotifyMessage;
use WezomCms\Core\Foundation\Notifications\NotifyDriverInterface;

class JsResponse implements Arrayable, Jsonable, Responsable
{
    private $response = [];

    /**
     * JsFeedback constructor.
     * @param  array  $data
     */
    public function __construct(array $data = [])
    {
        $this->setDefaults();

        $this->massAssigment($data);
    }

    /**
     * @param  array  $data
     * @return JsResponse
     */
    public static function make(array $data = [])
    {
        return new static($data);
    }

    /**
     * @param  bool  $success
     * @return JsResponse
     */
    public function success(bool $success): JsResponse
    {
        $this->response['success'] = $success;

        if ($success === false) {
            $this->response['reset'] = false;
        }

        return $this;
    }

    /**
     * @param  string|NotifyDriverInterface  $text
     * @param  string  $type
     * @param  int  $time
     * @return JsResponse
     */
    public function notification($text, string $type = 'success', int $time = 5): JsResponse
    {
        if ($text instanceof NotifyDriverInterface) {
            $this->response['notifications'][] = $text;
        } else {
            $notification = $this->buildNotification($text, $type, $time);

            $this->response['notifications'][] = $notification;
        }

        return $this;
    }

    /**
     * @return JsResponse
     */
    public function clearNotifications(): JsResponse
    {
        $this->response['notifications'] = [];

        return $this;
    }

    /**
     * @param $url
     * @return JsResponse
     */
    public function redirect($url): JsResponse
    {
        return $this->set('redirect', $url);
    }

    /**
     * @param  bool  $reload
     * @return JsResponse
     */
    public function reload($reload = true): JsResponse
    {
        return $this->set('reload', $reload);
    }

    /**
     * @param  array  $errors
     * @return JsResponse
     */
    public function errors(array $errors = []): JsResponse
    {
        return $this->set('errors', $errors);
    }

    /**
     * @param  bool  $reset
     * @return JsResponse
     */
    public function reset(bool $reset): JsResponse
    {
        return $this->set('reset', $reset);
    }

    /**
     * @param  array|string  $options
     * @return JsResponse
     */
    public function magnific($options): JsResponse
    {
        return $this->set('magnific', $options);
    }

    /**
     * @return JsResponse
     */
    public function setDefaults(): JsResponse
    {
        $defaults = [
            'success' => true,
            'notifications' => [],
            'reset' => true,
            'reload' => false,
        ];

        $this->massAssigment($defaults);

        return $this;
    }

    /**
     * @param  array  $data
     * @return JsResponse
     */
    public function massAssigment(array $data): JsResponse
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $response = $this->response;
        foreach ($response['notifications'] as &$notification) {
            $notification = $notification->toArray();
        }

        return $response;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json($this->toArray());
    }

    /**
     * @throws \Exception|JsResponseException|mixed
     */
    public function throwException()
    {
        $exception = new JsResponseException();
        $exception->setResponse($this);

        throw $exception;
    }

    /**
     * @param  string  $key
     * @param $value
     * @return JsResponse
     */
    public function set(string $key, $value): JsResponse
    {
        array_set($this->response, $key, $value);

        return $this;
    }

    /**
     * @param $text
     * @param  string  $type
     * @param  int  $time
     * @return NotifyDriverInterface|mixed
     */
    protected function buildNotification($text, string $type = 'success', int $time = 5)
    {
        return NotifyMessage::$type($text, $time);
    }
}
