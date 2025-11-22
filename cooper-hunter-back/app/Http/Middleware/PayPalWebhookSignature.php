<?php

namespace App\Http\Middleware;

use App\Dto\Payments\PayPalWebhookSignatureDto;
use App\Services\Payment\PayPalService;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayPalWebhookSignature
{

    public function __construct(private PayPalService $payPalService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return Response|RedirectResponse|JsonResponse
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        try {
            if (!$this->payPalService->verifyWebhook(PayPalWebhookSignatureDto::byRequest($request))) {
                throw new Exception();
            }
        } catch (Exception $e) {
            Log::error($e);
            return \response(status: Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
