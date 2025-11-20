<?php

namespace WezomCms\Core\Http\Middleware;

use Closure;
use Crawler;
use Illuminate\Http\Request;
use SpamProtector;

class CheckForSpam
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($request, Closure $next)
    {
        if (config('cms.core.main.protection.crawler') && Crawler::isCrawler()) {
            $this->log($request);

            return $this->makeFailResponse();
        }

        try {
            if (
                config('cms.core.main.protection.spam.email')
                && $request->has('email')
                && ($email = $request->get('email'))
            ) {
                if (SpamProtector::isSpamEmail($email)) {
                    $this->log($request);

                    return $this->makeFailResponse();
                }
            }
            if (
                config('cms.core.main.protection.spam.username')
                && $request->has('username')
                && ($username = $request->get('username'))
            ) {
                if (SpamProtector::isSpamUsername($username)) {
                    $this->log($request);

                    return $this->makeFailResponse();
                }
            }
            if (config('cms.core.main.protection.spam.ip') && SpamProtector::isSpamIp($request->ip())) {
                $this->log($request);

                return $this->makeFailResponse();
            }
        } catch (\Exception $e) {
        }

        return $next($request);
    }

    /**
     * @param  Request  $request
     */
    private function log($request)
    {
        logger('Spam attack', [
            'request' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    private function makeFailResponse()
    {
        $message = app('isBackend')
            ? __('cms-core::admin.auth.For security reasons your request has been canceled Please try again later')
            : __('cms-core::site.For security reasons your request has been canceled Please try again later');

        return response()->json([
            'success' => false,
            'message' => $message
        ]);
    }
}
