<?php

namespace App\GraphQL\Exceptions;

use App\Exceptions\ErrorsCode;
use Closure;
use GraphQL\Error\Error;
use Illuminate\Auth\AuthenticationException;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Nuwave\Lighthouse\Execution\ErrorHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ExtensionErrorHandler implements ErrorHandler
{
    public function __invoke(?Error $error, Closure $next): ?array
    {
        return $next($error);
    }

    public static function handle(Error $error, Closure $next): array
    {
        $underlyingException = $error->getPrevious();

        if($underlyingException instanceof AuthenticationException){

            $error = new Error(
                __('auth.not auth'),
                $error->nodes,
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                null,
                ['code' => ErrorsCode::NOT_AUTH]
            );

            return $next($error);
        }

        if($underlyingException instanceof UnauthorizedException){
            $error = new Error(
                __('auth.not perm'),
                $error->nodes,
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                null,
                ['code' => ErrorsCode::NOT_PERM]
            );

            return $next($error);
        }

        if ($underlyingException instanceof RendersErrorsExtensions) {
            // Reconstruct the error, passing in the extensions of the underlying exception
            $error = new Error(
                $error->message,
                $error->nodes,
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $underlyingException,
                $underlyingException->extensionsContent()
            );
        }

        return $next($error);
    }
}
