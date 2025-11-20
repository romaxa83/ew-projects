<?php

namespace WezomCms\Core\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Validation\ValidationException;

class FormThrottleRequest extends ThrottleRequests
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decaySeconds
     * @param  string  $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, $maxAttempts = null, $decaySeconds = null, $prefix = '')
    {
        if (null === $maxAttempts) {
            $maxAttempts = config('cms.core.main.form_throttle.max_attempts', 1);
        }

        if (null === $decaySeconds) {
            $decaySeconds = config('cms.core.main.form_throttle.decay_seconds', 10);
        }

        $key = $prefix . $this->resolveRequestSignature($request);

        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            throw $this->buildException($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decaySeconds);

        $response = $next($request);

        // Reset throttle counter if validation is failed.
        if (property_exists($response, 'exception') && $response->exception instanceof ValidationException) {
            $key = $this->resolveRequestSignature($request);

            $this->limiter->clear($key);
        }

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
}
