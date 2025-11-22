<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {

        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }
        return parent::render($request, $e);
    }

    private function handleApiException($request, Throwable $exception): JsonResponse
    {
        $exception = $this->prepareException($exception);
        if ($exception instanceof HttpResponseException) {
            $exception = $exception->getResponse();
        }

        if ($exception instanceof AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }

        return $this->customApiResponse($exception);
    }

    private function customApiResponse($exception): JsonResponse
    {
//        dd($exception);

        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } elseif (method_exists($exception, 'getCode')) {
            if(!is_numeric($exception->getCode())){
                // ошибки с бд
                $statusCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $statusCode = $exception->getCode();
            }
        }
        else {
            $statusCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        if($statusCode < 100 || $statusCode >= 600){
            $statusCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        $response = [];
        switch ($statusCode) {
            case HttpResponse::HTTP_NOT_ACCEPTABLE;
            case HttpResponse::HTTP_UNAUTHORIZED:
                $response['errors'][] = [
                    'title' => $exception->original['message'] ?? $exception->getMessage(),
                    'status' => $statusCode
                ];
                break;

            case HttpResponse::HTTP_BAD_REQUEST:
                $response['errors'][] = [
                    'title' => method_exists($exception, 'getMessage') && $exception->getMessage() !== ''
                        ? $exception->getMessage()
                        : 'Bad Request',
                    'status' => $statusCode
                ];
                break;
            case  HttpResponse::HTTP_FORBIDDEN:
                $response['errors'][] = [
                    'title' => method_exists($exception, 'getMessage') && $exception->getMessage() !== ''
                        ? $exception->getMessage()
                        : 'Forbidden',
                    'status' => $statusCode
                ];
                break;

            case 404:
                $response['errors'][] = [
                    'title' => method_exists($exception, 'getMessage') && $exception->getMessage() !== ''
                        ? $exception->getMessage()
                        : 'Page Not Found',
                    'status' => $statusCode
                ];
                break;

            case 405:
                $response['errors'][] = [
                    'title' => method_exists($exception, 'getMessage') && $exception->getMessage() !== ''
                        ? $exception->getMessage()
                        : 'Method Not Allowed',
                    'status' => $statusCode
                ];
                break;

            case 422:
                $errorBag = [];
                if (isset($exception->original['errors'])) {

                    foreach ($exception->original['errors'] as $key => $error) {
                        $errorBag['source'] = ['parameter' => $key];
                        $errorBag['title'] = \Arr::get($error, 0, $exception->original['message']);
                        $errorBag['status'] = $statusCode;
                        $response['errors'][] = $errorBag;
                    }
                }
                break;

            case 200: // only validated
                $errorBag = [];
                if (isset($exception->original['errors']) && count(($exception->original['errors']))) {
                    foreach ($exception->original['errors'] as $key => $error) {
                        $errorBag['source'] = ['parameter' => $key];
                        $errorBag['title'] = \Arr::get($error, 0, $exception->original['message']);
                        $errorBag['status'] = $statusCode;
                        $response['data'][] = $errorBag;
                    }
                } else {
                    $response['data'] = [];
                }
                break;

            default:
                $response['errors'][] = ['title' => 'The backend responded with an error', 'status' => $statusCode];
                $response['trace']['message'] = $exception->getMessage();
                $response['trace']['file'] = $exception->getFile();
                $response['trace']['line'] = $exception->getLine();
                $response['trace']['trace'] = $exception->getTraceAsString();

                break;
        }

//        dd($response);

        return response()->json($response, $statusCode);
    }
}
