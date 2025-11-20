<?php

namespace WezomCms\Supports\Http\Controllers\Api\V1;

use Notification;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Models\Administrator;
use WezomCms\Supports\Http\Requests\Api\SupportRequest;
use WezomCms\Supports\Notifications\SendSupportMessageNotifications;
use WezomCms\Supports\Services\SupportService;

class SupportController extends ApiController
{
    private SupportService $supportService;

    public function __construct(SupportService $supportService)
    {
        parent::__construct();
        $this->supportService = $supportService;
    }

    public function create(SupportRequest $request)
    {
        try {

            $message = $this->supportService->create($request->all());

            $administrators = Administrator::toNotifications('supports.index')->get();
            Notification::send($administrators, new SendSupportMessageNotifications($message));

            return $this->successEmptyMessage();

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }
}
