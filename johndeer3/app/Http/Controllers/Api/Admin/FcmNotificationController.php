<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Notification\RequestFcmNotificationTemplateEdit;
use App\Repositories\FcmNotification\FcmNotificationRepository;
use App\Resources\Notification\NotificationTemplateResource;
use App\Services\FcmNotification\TemplateService;
use Illuminate\Http\Request;

class FcmNotificationController extends ApiController
{
    public function __construct(
        protected FcmNotificationRepository $repo,
        protected TemplateService $service
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/admin/fcm-notification-templates",
     *     tags={"Notifications"},
     *     summary="Получить все шаблоны уведомлений",
     *     security={{"Basic": {}}},
     *
     *      @OA\Response(response="200", description="Шаблоны",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/NotificationTemplateResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function index()
    {
        try {
            return $this->successJsonMessage(
                NotificationTemplateResource::collection(
                    $this->repo->getAll(["translations"])
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/fcm-notification-templates/{template}",
     *     tags={"Notifications"},
     *     summary="Получить шаблон по ID",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{template}", in="path", required=true,
     *          description="ID шаблона",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *      @OA\Response(response="200", description="Шаблоны",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/NotificationTemplateResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function show(Request $request, $id)
    {
        try {
            return $this->successJsonMessage(
                NotificationTemplateResource::make(
                    $this->repo->getByID($id)
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/fcm-notification-templates/update/{template}",
     *     tags={"Notifications"},
     *     summary="Редактирование шаблона",
     *     description="Редактирует переводы шаблона или добавляет новые, если такой локали нет",
     *     security={{"Basic": {}}},
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/RequestFcmNotificationTemplateEdit")
     *     ),
     *
     *      @OA\Response(response="200", description="Шаблоны",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/NotificationTemplateResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function edit(RequestFcmNotificationTemplateEdit $request, $id)
    {
        try {
            return $this->successJsonMessage(
                NotificationTemplateResource::make(
                    $this->service->edit(
                        $request->all(),
                        $this->repo->getByID($id)
                    )
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}


