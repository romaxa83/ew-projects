<?php

namespace App\Http\Controllers\V1\Saas\Intl;

use App\Http\Controllers\ApiController;
use App\Http\Requests\ChangeLanguageRequest;
use App\Http\Resources\ChangeLanguageResource;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LanguageController extends ApiController
{
    public static function getLanguagesList()
    {
        $data = Language::get();

        $data->transform(function ($el) {
            unset($el['created_at'], $el['updated_at']);

            return $el;
        });

        return $data;
    }

    /**
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/v1/saas/languages",
     *     tags={"V1 Saas LanguageService"},
     *     summary="Get languages  list",
     *     operationId="Get languages data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/LanguagesList")
     *     ),
     * )
     */
    public function index()
    {
        return LanguageResource::collection(
            self::getLanguagesList()
        );
    }

    /**
     * @param Language $language
     * @return LanguageResource
     *
     * @OA\Get(
     *     path="/v1/saas/languages/{languageId}",
     *     tags={"V1 Saas LanguageService"},
     *     summary="Get info about language",
     *     operationId="Get admin language",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Language")
     *     ),
     * )
     */
    public function show(Language $language)
    {
        return new LanguageResource($language);
    }

    /**
     * @param ChangeLanguageRequest $request
     * @return ChangeLanguageResource|JsonResponse
     *
     * @OA\Put(
     *     path="/v1/saas/languages/change-language",
     *     tags={"V1 Saas LanguageService"},
     *     summary="Change language for user",
     *     operationId="Change language for auth user",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(
     *          name="language",
     *          in="query",
     *          description="Language slug",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="en",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ChangeLanguage")
     *     ),
     * )
     */
    public function changeLanguage(ChangeLanguageRequest $request)
    {
        $user = $request->user();
        if ($user->changeLanguage($request->input('language'))) {
            return new ChangeLanguageResource($user);
        }
        return $this->makeErrorResponse(null, 500);
    }

    /**
     * @param Request $request
     * @return LanguageResource|JsonResponse
     *
     * @OA\Get(
     *     path="/v1/saas/languages/selected",
     *     tags={"V1 Saas LanguageService"},
     *     summary="Get language selected by user",
     *     operationId="Get language selected by user",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Language")
     *     ),
     * )
     */
    public function getSelected(Request $request)
    {
        if ($request->user()->language) {
            return LanguageResource::make(
                Language::where('slug', $request->user()->language)->firstOrFail()
            );
        }

        return new LanguageResource(
            Language::whereDefault(true)->firstOrFail()
        );
    }
}
