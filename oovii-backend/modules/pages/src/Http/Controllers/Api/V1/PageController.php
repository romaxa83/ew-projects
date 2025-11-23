<?php

namespace WezomCms\Pages\Http\Controllers\Api\V1;

use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Pages\Http\Resources\V1\PageResource;
use WezomCms\Pages\Repositories\PageRepository;

class PageController extends ApiController
{
    public function __construct(
        protected PageRepository $repo
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/pages",
     *     tags={"Page"},
     *     summary="Get all info pages",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/PageResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list()
    {
        try {
            $models = $this->repo->getAll(["translation"]);

            return self::successJsonMessage(PageResource::collection($models));
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            return self::successJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}

