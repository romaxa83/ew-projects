<?php

namespace App\GraphQL\Middlewares\Security;

use App\Services\Security\IpAccessService;
use Closure;
use Core\ValueObjects\IpAddressValueObject;
use Illuminate\Http\Request;
use Rebing\GraphQL\Error\AuthorizationError;

class IpAccessMiddleware
{
    public function __construct(private IpAccessService $service)
    {
    }

    /**
     * @throws AuthorizationError
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!config('security.ip-access.enabled')) {
            return $next($request);
        }

        $ipAddress = new IpAddressValueObject($request->ip());

        if ($this->service->check($ipAddress)) {
            return $next($request);
        }

        throw new AuthorizationError('You ip is not allowed!');
    }
}
