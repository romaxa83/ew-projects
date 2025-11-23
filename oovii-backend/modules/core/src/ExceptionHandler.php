<?php

namespace WezomCms\Core;

use Flash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use LaravelLocalization;
use Request;
use Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use WezomCms\Core\Foundation\JsResponseException;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\TelegramBot\Telegram;

class ExceptionHandler extends Handler
{
    use AjaxResponseStatusTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        JsResponseException::class,
    ];

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {

            Telegram::error($exception, null, $request);

            if(isApiRequest($request)){
                return ApiController::errorJsonMessage(
                    __('cms-core::site.Unauthenticated'),
                    Response::HTTP_UNAUTHORIZED
                );
            }

            return response()->json(['message' => __('cms-core::site.Unauthenticated')], 401);
        }
        $guard = array_get($exception->guards(), 0);
        switch ($guard) {
            case 'admin':
                Telegram::error($exception, null, $request);

                return redirect()->guest(route('admin.login-form'));
            default:
                Telegram::error($exception, null, $request);
                Flash::warning(__('cms-core::site.To enter your Personal Account you need to log in'));

                $redirect = Route::getRoutes()->hasNamedRoute('home')
                    ? route('home')
                    : LaravelLocalization::localizeURL('/');

                if (($livewire = app('livewire')) && $livewire->isLivewireRequest()) {
                    return response()->json(['effects' => compact('redirect')]);
                }

                return redirect()->guest($redirect);
        }
    }

    /**
     * Render the given HttpException.
     *
     * @param HttpExceptionInterface $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        $statusCode = $e->getStatusCode();

        app('seotools')->setTitle($statusCode);

        if (419 === $statusCode && app('isBackend')) {
            return redirect()->back()
                ->withInput(Request::except(array_merge($this->dontFlash, ['_token'])))
                ->withErrors(__('cms-core::admin.CSRF token mismatch'));
        }

        return parent::renderHttpException($e);
    }


    /**
     * Register the error template hint paths.
     *
     * @return void
     */
    protected function registerErrorViewPaths()
    {
        (new RegisterErrorViewPaths())();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  Throwable  $e
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        Telegram::error($e, null, $request);
        if ($e instanceof AccessDeniedHttpException) {
            return $this->error(__('cms-core::admin.auth.Access is denied!'));
        }

        if(isApiRequest($request)) {
            return ApiController::errorJsonMessage(
                $e->getMessage(),
                errorCode($e)
            );
        }

        return parent::prepareJsonResponse($request, $e);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        if ($e instanceof NotFoundHttpException) {
            $message = __('cms-core::site.Page not found');
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $message = __('cms-core::site.Method not allowed');
        } elseif ($e instanceof ThrottleRequestsException) {
            $seconds = array_get($e->getHeaders(), 'Retry-After');
            if ($seconds) {
                $message = __('cms-core::site.To many attempts Retry after :seconds seconds', ['seconds' => $seconds]);
            } else {
                $message = __('cms-core::site.To many attempts');
            }
        } elseif ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 419) {
            $message = __('cms-core::site.CSRF token mismatch');
        } else {
            $message = __('cms-core::site.Server error');
        }

        if (config('app.debug')) {
            return [
                'message' => $message,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
            ];
        }

        return compact('message');
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        Telegram::error($exception, null, $request);
        if(isApiRequest($request)){
            $msg = current($exception->errors());
            if(is_array($msg)){
                $msg = current($msg);
            }

            return ApiController::errorJsonMessage($msg, Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('cms-core::site.The given data was invalid'),
            'errors' => $exception->errors(),
        ], $exception->status);
    }
}
