<?php

namespace Core\Exceptions;

use App\Http\Controllers\Api\ApiController;
use GraphQL\Error\Error;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function React\Promise\Stream\first;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(static function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof Error) {
            return $this->formatToGraphQL($e);
        }

        if(str_contains($request->getPathInfo(), '/api/v1/')){

            $msg = null;
            $code = $e->getCode();
            if($e instanceof ValidationException){
                foreach ($e->errors() as $error){
                    $msg = current($error);
                    break;
                }
                $code = 400;
            } else {
                $msg = $e->getMessage();
            }

            return ApiController::errorJsonMessage($msg, $code);
        }

        return parent::render($request, $e);
    }

    protected function formatToGraphQL(Throwable $e): JsonResponse
    {
        $body = [
            'errors' => [
                call_user_func(config('graphql.error_formatter'), $e),
            ],
        ];

        $headers = config('graphql.headers', []);
        $jsonOptions = config('graphql.json_encoding_options', 0);

        return response()->json($body, 200, $headers, $jsonOptions);
    }
}
