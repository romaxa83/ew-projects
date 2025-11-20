<?php

namespace WezomCms\Core;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use View;
use WezomCms\Core\Api\ErrorCode;
use WezomCms\Core\Foundation\JsResponseException;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;

class ExceptionHandler extends Handler
{
    use AjaxResponseStatusTrait;

    protected $dontReport = [
        JsResponseException::class,
    ];

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {

        if ($request->expectsJson()) {

            return response()->json(
                [
                    'error' => [
                        'message' => __('cms-core::site.Unauthenticated'),
                        'code' => ErrorCode::NOT_VALID_ACCESS_TOKEN,
                    ]
                ],
                401
            );
//            return response()->json(['message' => __('cms-core::site.Unauthenticated')], 401);
        }
        $guard = array_get($exception->guards(), 0);
        switch ($guard) {
            case 'admin':
                return redirect()->guest(route('admin.login-form'));
                break;
            default:
                return redirect()->guest('/');
                break;
        }
    }

    /**
     * Render the given HttpException.
     *
     * @param \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        return parent::renderHttpException($e);
    }


    /**
     * Register the error template hint paths.
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function registerErrorViewPaths()
    {
        $paths = collect(config('view.paths'));

        $hints = app('view')->getFinder()->getHints();

        // Add ui path if present
        if ($uiPath = array_get($hints, 'cms-ui.0')) {
            $paths->prepend($uiPath);
        }

        // Add core backend path
        if (app('isBackend') && $corePath = array_get($hints, 'cms-core.0')) {
            $paths->prepend($corePath . '/admin');
        }

        $laravelExceptionViews = dirname((new \ReflectionClass(Handler::class))
                ->getFileName()) . '/views';

        View::replaceNamespace('errors', $paths->map(function ($path) {
            return "{$path}/errors";
        })->push($laravelExceptionViews)->all());
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        if ($e instanceof AccessDeniedHttpException) {
            return $this->error(__('cms-core::admin.auth.Access is denied!'));
        } elseif ($request->is('api/*')){

            return response()->json([
                'error' => [
                    'code' => 1,
                    'message' => $e->getMessage(),
                ]
            ], 500);
        } else {
            return parent::prepareJsonResponse($request, $e);
        }
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
            $retryAfter = array_get($e->getHeaders(), 'Retry-After');
            if ($retryAfter) {
                $message = __('cms-core::site.To many attempts Retry after :seconds seconds', ['seconds' => $retryAfter]);
            } else {
                $message = __('cms-core::site.To many attempts');
            }
        } elseif ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 419) {
            $message = __('cms-core::site.CSRF token mismatch');
        } else {
            $message = __('cms-core::site.Server error');
        }

        return config('app.debug') ? [
            'message' => $message,
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : ['message' => $message];
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $message = [];
        foreach ($exception->errors() as $field => $errors){
            foreach($errors as $mes){
                $message[] = $mes . '(' . $field . ')';
            }
        }

        if(in_array('1c', explode('/', $request->url())) || in_array('1c-test', explode('/', $request->url()))){
            return response()->json([
                'success' => false,
                'message' => $message[0] ?? null,
            ], $exception->status);
        }

        return response()->json([
            'error' => [
                'code' => 0,
                'message' => $message[0] ?? null,
            ]
        ], $exception->status);

//        return response()->json([
//            'message' => __('cms-core::site.The given data was invalid'),
//            'errors' => $exception->errors(),
//        ], $exception->status);
    }
}
