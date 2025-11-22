<?php

namespace App\Http\Controllers\Api\V1\Inventories\Category;

use App\Events\Events\Inventories\Categories\UpdateImageCategoryEvent;
use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Models\Inventories\Category;
use App\Repositories\Inventories\CategoryRepository;
use App\Services\Inventories\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UploadController extends ApiController
{
    public function __construct(
        protected CategoryRepository $repo,
        protected CategoryService $service,
    )
    {}

    /**
     * @OA\Delete(
     *     path="/api/v1/inventory-categories/{id}/images/{imageId}",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Delete image from category",
     *     operationId="DeleteImageFromCategory",
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
        $this->authorize(Permission\Inventory\Category\CategoryUpdatePermission::KEY);

        /** @var $model Category */
        $model = $this->repo->getById($id);

        $this->service->deleteFile($model, $imageId);

        event(new UpdateImageCategoryEvent($model));

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
