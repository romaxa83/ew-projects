<?php

namespace App\Exceptions;

use App\Exceptions\Billing\HasUnpaidInvoiceException;
use Arr;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse|Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }
        return parent::render($request, $e);
    }

    /**
     * @param $request
     * @param Throwable $exception
     * @return JsonResponse
     */
    private function handleApiException($request, Throwable $exception)
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

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse|Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return parent::unauthenticated($request, $exception);
    }

    /**
     * @param $exception
     * @return JsonResponse
     */
    private function customApiResponse($exception)
    {

        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }

        if($exception instanceof HasUnpaidInvoiceException){
            $statusCode = 401;
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

            case 403:
                $response['errors'][] = [
                    'title' => method_exists($exception, 'getMessage') ? $exception->getMessage() : 'Forbidden',
                    'status' => $statusCode
                ];
                break;

            case 404:
                $response['errors'][] = ['title' => 'Page Not Found'];
                break;

            case 405:
                $response['errors'][] = [
                    'title' => method_exists($exception, 'getMessage') ? $exception->getMessage(
                    ) : 'Method Not Allowed',
                    'status' => $statusCode
                ];
                break;

            case 422:
                $errorBag = [];
                if (isset($exception->original['errors'])) {
                    foreach ($exception->original['errors'] as $key => $error) {
                        $errorBag['source'] = ['parameter' => $key];
                        $errorBag['title'] = Arr::get($error, 0, $exception->original['message']);
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
                        $errorBag['title'] = Arr::get($error, 0, $exception->original['message']);
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

//                logger_info('EL', [$exception]);

                break;
        }

        return response()->json($response, $statusCode);
    }
}
