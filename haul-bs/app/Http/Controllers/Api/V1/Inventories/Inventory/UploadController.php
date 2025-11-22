<?php

namespace App\Http\Controllers\Api\V1\Inventories\Inventory;

use App\Events\Events\Inventories\Inventories\UpdateImageInventoryEvent;
use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Requests\Common\SingleImageRequest;
use App\Http\Resources\Inventories\Inventory\InventoryResource;
use App\Models\Inventories\Inventory;
use App\Repositories\Inventories\InventoryRepository;
use App\Services\Inventories\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UploadController extends ApiController
{
    public function __construct(
        protected InventoryRepository $repo,
        protected InventoryService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/inventories/{id}/gallery",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Add single image to inventory gallery (not remove old images)",
     *     operationId="AddSingleImageToInventoryGallery",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="image", type="string", format="binary",)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function upload(SingleImageRequest $request, $id): InventoryResource
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryUpdatePermission::KEY);

        /** @var $model Inventory */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        event(new UpdateImageInventoryEvent($model));

        return InventoryResource::make(
            $this->service->uploadImage($model, $request->image, Inventory::GALLERY_FIELD_NAME)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/inventories/{id}/images/{imageId}",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Delete image from inventory",
     *     operationId="DeleteImageFromInventory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="{imageId}", in="path", required=true, description="ID media entity",
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
    public function delete($id, $imageId): JsonResponse
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryUpdatePermission::KEY);

        /** @var $model Inventory */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        $this->service->deleteFile($model, $imageId);

        event(new UpdateImageInventoryEvent($model));

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
