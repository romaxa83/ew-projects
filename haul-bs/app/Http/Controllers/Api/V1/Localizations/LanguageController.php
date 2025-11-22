<?php

namespace App\Http\Controllers\Api\V1\Localizations;

use App\Foundations\Modules\Localization\Repositories\LanguageRepository;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Localizations\LanguageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LanguageController extends ApiController
{
    public function __construct(
        protected LanguageRepository $repo
    )
    {}

    /**
     * @OA\Get (
     *     path="/api/v1/languages",
     *     tags={"Localization"},
     *     summary="Get languages",
     *     operationId="GetLanguages",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\Response(response=200, description="Language data as list",
     *          @OA\JsonContent(ref="#/components/schemas/LanguageResourceList")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function list(): JsonResponse|AnonymousResourceCollection
    {
        try {
            return LanguageResource::collection($this->repo->getLanguages());
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
