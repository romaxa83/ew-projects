<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

class ValidationException extends \Illuminate\Validation\ValidationException
{
    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = Response::HTTP_OK;

    /**
     * Create a new exception instance.
     *
     * @param  Validator  $validator
     * @param  \Symfony\Component\HttpFoundation\Response|null  $response
     * @param  string  $errorBag
     * @return void
     */
    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        Exception::__construct('Data is validated.');

        $this->response = $response;
        $this->errorBag = $errorBag;
        $this->validator = $validator;
    }
}
