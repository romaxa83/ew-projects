<?php

namespace Core\WebSocket\Services;

use App\Models\BaseAuthenticatable;
use Core\WebSocket\Contracts\Subscribable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use ReflectionClass;
use ReflectionException;
use Throwable;

abstract class WsAuthService
{
    public function getUserByBearer(string $bearer): BaseAuthenticatable|Authenticatable|Subscribable|null
    {
        try {
            $user = $this->login($bearer);
            $this->logout();

            return $user;
        } catch (Throwable $e) {
            logger($e);
            return null;
        }
    }

    protected function login(string $bearer): BaseAuthenticatable|Authenticatable|Subscribable|null
    {
        request()->headers->set('Authorization', $bearer);
        $auth = $this->authGuard();
        $auth->setRequest(request());

        return $auth->user();
    }

    protected function authGuard(): Guard
    {
        return auth(static::GUARD);
    }

    /**
     * @throws ReflectionException
     */
    protected function logout(): void
    {
        $auth = $this->authGuard();

        $reflectionClass = new ReflectionClass($auth);
        $reflectionProperty = $reflectionClass->getProperty('user');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($auth, null);

        request()->headers->remove('Authorization');
        $auth->setRequest(request());
    }
}
