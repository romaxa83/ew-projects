<?php

namespace App\Http\Controllers\Api\V1\Common;

use App\Foundations\Modules\Seo\Models\Seo;
use App\Foundations\Modules\Seo\Repositories\SeoRepository;
use App\Foundations\Modules\Seo\Services\SeoService;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SeoController extends ApiController
{
    public function __construct(
        protected SeoRepository $repo,
        protected SeoService $service,
    )
    {}

    /**
     * @OA\Delete(
     *     path="/api/v1/seo/{id}/image/{imageId}",
     *     tags={"Seo"},
     *     security={{"Basic": {}}},
     *     summary="Delete image from seo data",
     *     operationId="DeleteImageFromSeoData",
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
        /** @var $model Seo */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.seo.not_found")
        );

        $this->service->deleteFile($model, $imageId);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
