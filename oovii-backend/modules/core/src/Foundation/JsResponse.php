<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\HtmlString;
use Livewire\Component;
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
     * Send command to modal.
     *
     * @param  array|string  $options
     *
     * Close modal:
     * $options = 'close'
     *
     * Render html content:
     * ['content' => '<span>some rendered html or Renderable or HtmlString</span>']
     *
     * Render livewire component:
     * ['component' => 'component-name']
     *
     * Render livewire component with parameters (for "mount()" method):
     * ['component' => ['name' => 'component-name', 'params' => ['param1', 'param2']]]
     *
     * @return JsResponse
     */
    public function modal($options): JsResponse
    {
        if (is_array($options) && isset($options['content']) && !is_string($options['content'])) {
            if ($options['content'] instanceof Renderable) {
                $options['content'] = $options['content']->render();
            } elseif ($options['content'] instanceof HtmlString) {
                $options['content'] = $options['content']->toHtml();
            }
        }

        return $this->set('modal', $options);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        return response()->json($this->toArray());
    }

    /**
     * @param  Component  $component
     */
    public function emit(Component $component)
    {
        $this->set('componentId', $component->id);

        $component->emit('jsResponse', $this->toArray());
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
