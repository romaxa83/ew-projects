<?php


namespace App\Http\Controllers\Webhook;


use App\Http\Controllers\Controller;
use App\Services\Payment\PayPalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayPalController extends Controller
{
    public function __construct(private PayPalService $payPalService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->payPalService->webhookProcessing($request->input())) {
            return response()->json(status: Response::HTTP_FORBIDDEN);
        }

        return response()->json();
    }
}
