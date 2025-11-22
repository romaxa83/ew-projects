<?php

namespace App\Http\Controllers\Api\V1\Orders\BS;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Common\SingleAttachmentRequest;
use App\Http\Resources\Orders\BS\OrderResource;
use App\Models\Orders\BS\Order;
use App\Repositories\Orders\BS\OrderRepository;
use App\Services\Orders\BS\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UploadController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected OrderService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs/{id}/attachments",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Add single attachment to bs order",
     *     operationId="AddSingleAttachmentToBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="attachment", in="query", description="attachment file", required=false,
     *         @OA\Schema(type="file",)
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function upload(SingleAttachmentRequest $request, $id): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\BS\OrderUpdatePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
        );

        if ($model->status->isFinished()) {
            return $this->errorJsonMessage(
                __("exceptions.orders.bs.finished_order_cant_be_edited"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return OrderResource::make(
            $this->service->addAttachment($model, $request->attachment, true)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/bs/{id}/attachments/{attachmentId}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Delete attachment from bs order",
     *     operationId="DeleteAttachmentFromBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="{attachmentId}", in="path", required=true, description="ID attachment entity",
     *          @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id, $attachmentId): JsonResponse
    {
        $this->authorize(Permission\Order\BS\OrderUpdatePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
        );

        if ($model->status->isFinished()) {
            return $this->errorJsonMessage(
                __("exceptions.orders.bs.finished_order_cant_be_edited"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->deleteFile($model, $attachmentId, true);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
