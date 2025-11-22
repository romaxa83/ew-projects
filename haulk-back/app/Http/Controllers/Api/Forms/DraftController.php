<?php

namespace App\Http\Controllers\Api\Forms;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Forms\DraftResource;
use App\Services\Forms\DraftService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DraftController extends ApiController
{
    /**
     * @var DraftService
     */
    private $service;

    public function __construct(DraftService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @OA\Get(path="/api/forms/drafts/{path}", tags={"Drafts"}, summary="Get draft attributes for some form", operationId="Get drafted data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="path", in="path", description="Draft path", required=true,
     *          @OA\Schema(type="string", example="super_unique_slug")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Draft")
     *     ),
     *     @OA\Response(response=404, description="Draft not found"),
     * )
     *
     * @param string $path
     * @param Request $request
     * @return DraftResource
     * @throws Exception
     */
    public function show(string $path, Request $request): DraftResource
    {
        return DraftResource::make($this->service->show($request->user(), $path));
    }

    /**
     * @OA\Post(path="/api/forms/drafts/{path}", tags={"Drafts"}, summary="Store draft data from form", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="path", in="path", description="Draft type", required=true,
     *          @OA\Schema(type="string", example="contact")
     *     ),
     *     @OA\Parameter(name="field1", in="query", description="Some filed attribute for draft", required=true,
     *          @OA\Schema(type="string", example="Text for field1")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     *
     * @param string $path
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(string $path, Request $request): JsonResponse
    {
        $this->service->createOrUpdate($request->user(), $path, $request->except('path'));

        return $this->makeSuccessResponse(null, Response::HTTP_OK);
    }

    /**
     *
     * @OA\Delete(path="/api/forms/drafts/{path}", tags={"Drafts"}, summary="Delete draft for form", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="path", in="path", description="Draft type", required=true,
     *          @OA\Schema(type="string", example="contact")
     *     ),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     *
     * @param string $path
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(string $path, Request $request): JsonResponse
    {
        if ($this->service->delete($request->user(), $path)) {
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
