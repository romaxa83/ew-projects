<?php

namespace App\Http\Controllers\Api\V1\Localizations;

use App\Foundations\Modules\Localization\Enums\Translations\TranslationPlace;
use App\Foundations\Modules\Localization\Repositories\TranslationRepository;
use App\Foundations\Modules\Localization\Services\TranslationService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Localizations\SetTranslationRequest;
use App\Http\Requests\Localizations\TranslationFilterRequest;
use App\Http\Resources\Localizations\TranslationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TranslationController extends ApiController
{
    public function __construct(
        protected TranslationService $service,
        protected TranslationRepository $repo,
    )
    {}

    /**
     * @OA\Get (
     *     path="/api/v1/translations",
     *     tags={"Localization"},
     *     summary="Get translations",
     *     operationId="GetTranslations",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),

     *     @OA\Parameter(name="lang", in="query", required=false,
     *         description="Filter by lang",
     *         @OA\Schema(type="string", example="en")
     *     ),
     *
     *     @OA\Response(response=200, description="Translation data as list",
     *          @OA\JsonContent(ref="#/components/schemas/TranslationResourceList")
     *     ),
     *
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function list(TranslationFilterRequest $request): JsonResponse|AnonymousResourceCollection
    {
        if($request->validated('lang')){
            $lang = $request->validated('lang');
        } elseif (auth_user()){
            $lang = auth_user()->lang;
        } else {
            $lang = default_lang()->slug;
        }
        $filters = [
            'place' => TranslationPlace::SITE,
            'lang' => $lang
        ];

        try {
            return response()->json([
                'data' => $this->repo->getTranslationsAsArray(filters: $filters),
            ]);
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/v1/translations",
     *     tags={"Localization"},
     *     summary="Create or update translations",
     *     operationId="CreateOrUpdateTranslations",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *
     *     @OA\RequestBody(required=false,
     *           @OA\JsonContent(ref="#/components/schemas/SetTranslationRequest")
     *      ),
     *
     *     @OA\Response(response=200, description="Response message",
     *          @OA\JsonContent(ref="#/components/schemas/SimpleResponse")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="403", description="Forbidden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function create(SetTranslationRequest $request): JsonResponse
    {
        try {
            if(!$this->service->createOrUpdate($request->validated())){
                throw new \Exception("Something wrong");
            }

            return $this->successJsonMessage('Done');
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}

