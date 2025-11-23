<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Contracts\Support\Responsable;

class JsResponseException extends \Exception implements Responsable
{
    /**
     * @var JsResponse
     */
    protected $response;

    /**
     * @param  JsResponse  $response
     * @return JsResponseException
     */
    public function setResponse(JsResponse $response): JsResponseException
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return $this->response->toResponse($request);
    }
}
