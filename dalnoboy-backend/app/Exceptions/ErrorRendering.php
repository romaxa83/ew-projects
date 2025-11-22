<?php


namespace App\Exceptions;


use Core\Enums\Messages\AuthorizationMessageEnum;
use Core\Exceptions\TranslatedException;
use GraphQL\Error\Error;
use Illuminate\Http\Response;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Error\ValidationError;
use Rebing\GraphQL\GraphQL;

class ErrorRendering extends GraphQL
{
    public static function formatError(Error $e): array
    {
        $error = parent::formatError($e);

        $previous = $e->getPrevious();

        $error['errorCode'] = Response::HTTP_BAD_REQUEST;

        if ($previous && method_exists($previous, 'getResponseCode')) {
            $error['errorCode'] = $previous->getResponseCode();
        } elseif ($previous instanceof AuthorizationError) {
            $error['errorCode'] = $previous->getMessage() === AuthorizationMessageEnum::NO_PERMISSION ? Response::HTTP_FORBIDDEN :  Response::HTTP_UNAUTHORIZED;
        } elseif ($previous instanceof TranslatedException) {
            $error['errorCode'] = Response::HTTP_FAILED_DEPENDENCY;
        } elseif ($previous instanceof ValidationError) {
            $error['errorCode'] = Response::HTTP_UNPROCESSABLE_ENTITY;
        } elseif ($e->getNodes() && $e->getCategory() === Error::CATEGORY_GRAPHQL) {
            $error['errorCode'] = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return $error;
    }
}
