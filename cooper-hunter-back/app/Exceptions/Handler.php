<?php

namespace App\Exceptions;

use App\Contracts\Exceptions\Http\ApiAware;
use Illuminate\Http\JsonResponse;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends \Core\Exceptions\Handler
{
    protected $dontReport = [
        OAuthServerException::class,
        InvalidTokenStructure::class,
    ];

    public function render($request, Throwable $e): Response
    {
//        if($e instanceof  ValidationException){
//            dd($e->getMessage());
//            $message = [];
//            foreach ($exception->errors() as $errors){
//                foreach($errors as $mes){
//                    $message[] = $mes;
//                }
//            }
//        }

        if ($e instanceof ApiAware) {
            return $this->formatToHttp($e);
        }

        return parent::render($request, $e);
    }

    protected function formatToHttp(ApiAware $e): JsonResponse
    {
        return response()->json(
            [
                'category' => $e->getCategory(),
                'message' => $e->getMessage(),
            ],
            $e->getHttpCode()
        );
    }
}
