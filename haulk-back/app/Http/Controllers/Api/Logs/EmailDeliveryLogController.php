<?php

namespace App\Http\Controllers\Api\Logs;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Logs\EmailDeliveryLogsRequest;
use App\Services\Logs\DeliveryLogService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EmailDeliveryLogController extends AuthController
{
    private DeliveryLogService $deliveryLogService;

    /**
     * EmailDeliveryLogController constructor.
     * @param DeliveryLogService $deliveryLogService
     */
    public function __construct(DeliveryLogService $deliveryLogService)
    {
        parent::__construct();

        $this->deliveryLogService = $deliveryLogService;
    }

    public function receive(EmailDeliveryLogsRequest $request): JsonResponse
    {
        try {
            $this->deliveryLogService->processEmailLogs($request->input('logs'));

            return $this->makeSuccessResponse();
        } catch (Exception $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
