<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Files\FileResource;
use App\Models\Files\File;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Spatie\MediaLibrary\Models\Media;
use Storage;

class FileManageController extends ApiController
{
    protected const PER_PAGE = 50;

    /**
     * @OA\Get(path="/api/files", tags={"FileManage"}, summary="Get files paginated list", operationId="Get files data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Files per page", required=false,
     *          @OA\Schema(type="integer", default="50")
     *     ),
     *     @OA\Parameter(name="name", in="query", description="File name", required=false,
     *          @OA\Schema(type="string", default="",)
     *     ),
     *     @OA\Parameter(name="model_type", in="query", description="Model type", required=false,
     *          @OA\Schema(type="string", example="App/Models/Users/User",)
     *     ),
     *     @OA\Parameter(name="model_id", in="query", description="Scope for filter by status", required=false,
     *          @OA\Schema(type="integer", default="null",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FilePaginate")
     *     ),
     * )
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return FileResource::collection(
            Media::query()->paginate($request->get('pre_page', self::PER_PAGE))
        );
    }

    /**
     * @param File $file
     * @return JsonResponse
     * @throws Exception
     *
     * @OA\Delete(path="/api/files/{fileId}", tags={"FileManage"},
     *     summary="Delete file in archive", operationId="Delete admin in archive", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */
    public function delete(File $file): JsonResponse
    {
        try {
            Storage::delete($file->getPath());
            $file->delete();

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
