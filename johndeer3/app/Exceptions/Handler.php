<?php

namespace App\Exceptions;

use App\Services\Telegram\TelegramDev;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // переопределяем сообщения об ошибках
        if ($request->ajax() || $request->wantsJson())
        {
            $message = $exception->getMessage() ;

            TelegramDev::warn($message, "SYSTEM", $exception->getFile());

            // переопределяем статус код для неавторизованого пользователя
            if($exception instanceof AuthenticationException){
                return response()->json([
                    'success' => false,
                    'data' => $exception->getMessage()
                ], 401);
            }

            // при ошибки валидации формируем массив ошибок
            if($exception instanceof  ValidationException){
                $message = [];
                foreach ($exception->errors() as $errors){
                    foreach($errors as $mes){
                        $message[] = $mes;
                    }
                }
            }

            return response()->json([
                'success' => false,
                'data' => $message
            ], 400);
        }


        return parent::render($request, $exception);
    }
}
