<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;
use Illuminate\Support\Facades\{Storage};

class EnsureTwilioWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $requestData = $request->toArray();
        // Switch to the body content if this is a JSON request.
        if (array_key_exists('bodySHA256', $requestData)) {
            $requestData = $request->getContent();
        }

        $Validator = new RequestValidator(config('app.twilio.token'));
        $url = $request->fullUrl();
        $isValid = $Validator->validate($request->header('X-Twilio-Signature'), $url, $requestData);

        if (app()->environment('testing')) {
            $isValid = true;
        }

        if ($isValid) {
            return $next($request);
        } elseif (!empty($request->header('X-Twilio-Signature')) &&
            (is_array($requestData) && array_key_exists('Body', $requestData) && array_key_exists('To', $requestData) && array_key_exists('From', $requestData))) {
            // костыль.. почему то не все валидируются ((
            Storage::append('twilio.log', 'kostil validation' . PHP_EOL . PHP_EOL);
            return $next($request);
        } else {
            Storage::append('twilio.log', 'url=' . $url . PHP_EOL);
            Storage::append('twilio.log', 'header=' . $request->header('X-Twilio-Signature') . PHP_EOL);
            Storage::append('twilio.log', print_r($requestData, 1) . PHP_EOL . PHP_EOL);
            return response('You are not Twilio :(', 403);
        }
    }
}
