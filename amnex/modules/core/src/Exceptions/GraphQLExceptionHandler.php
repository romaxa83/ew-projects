<?php

namespace Wezom\Core\Exceptions;

use Closure;
use GraphQL\Error\ClientAware;
use GraphQL\Error\Error;
use Illuminate\Auth\Access\AuthorizationException as LaravelAuthorizationException;
use Illuminate\Auth\AuthenticationException as LaravelAuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Nuwave\Lighthouse\Execution\ErrorHandler;
use Throwable;
use Wezom\Core\Enums\GraphQLErrorClassification;

class GraphQLExceptionHandler implements ErrorHandler
{
    public function __invoke(?Error $error, Closure $next): ?array
    {
        if (null === $error) {
            return $next(null);
        }

        $previous = $error->getPrevious();

        //        dd($error);

        return $next(
            new GraphQLClassifiedException(
                $this->getClassification($previous),
                new Error(
                    $this->getMessage($previous),
                    $error->getNodes(),
                    $error->getSource(),
                    $error->getPositions(),
                    $error->getPath(),
                    $this->isClientSafe($previous) ? $previous : null,
                    $error->getExtensions()
                )
            )
        );
    }

    private function getClassification(?Throwable $e): GraphQLErrorClassification
    {
        if (null === $e) {
            return GraphQLErrorClassification::INTERNAL_ERROR;
        }

        return match (true) {
            $this->isUnauthorized($e) => GraphQLErrorClassification::UNAUTHORIZED,
            $this->isBadRequest($e) => GraphQLErrorClassification::BAD_REQUEST,
            $this->isForbidden($e) => GraphQLErrorClassification::FORBIDDEN,
            $this->isNotFound($e) => GraphQLErrorClassification::NOT_FOUND,
            default => GraphQLErrorClassification::INTERNAL_ERROR,
        };
    }

    private function getMessage(?Throwable $e): string
    {
        if ($e && ($this->isClientSafe($e) || $this->isNotFound($e) || $this->isForbidden($e))) {
            return $e->getMessage();
        }

        return __('core::exceptions.Something went wrong');
    }

    private function isBadRequest(Throwable $e): bool
    {
        return $e instanceof LaravelValidationException || $e instanceof ValidationException;
    }

    private function isUnauthorized(Throwable $e): bool
    {
        return $e instanceof LaravelAuthenticationException;
    }

    private function isForbidden(Throwable $e): bool
    {
        return $e instanceof LaravelAuthorizationException || $e instanceof UnauthorizedException;
    }

    private function isNotFound(Throwable $e): bool
    {
        return $e instanceof ModelNotFoundException;
    }

    private function isClientSafe(Throwable $e): bool
    {
        return $e instanceof ClientAware && $e->isClientSafe();
    }
}
