<?php

namespace App\Http\Controllers\Api\Translates;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Translates\TranslateRequest;
use App\Http\Requests\Translates\TranslateSyncRequest;
use App\Http\Resources\Translates\TranslateMultiLangResource;
use App\Http\Resources\Translates\TranslateResource;
use App\Models\Language;
use App\Models\Translates\Translate;
use App\Models\Users\User;
use App\Services\Translates\TranslateService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Log;
use Throwable;

class TranslateController extends ApiController
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/translates-list",
     *     tags={"Translates"},
     *     summary="Get translates list",
     *     operationId="Get translates data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="language", in="query", description="Language slug", required=false,
     *          @OA\Schema(type="string", default="en")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TranslateList")
     *     ),
     * )
     */
    public function list(Request $request)
    {
        $collection = new Collection();
        $lang = app()->getLocale();

        $language = $request->input('language');
        /** @var User $user */
        $user = $request->user();

        if (
            $language
            && preg_match('/[a-z]{2}/i', $language)
            && Language::whereSlug($language)->count()
        ) {
            $lang = $language;
        } elseif (
            $user
            && $user->language
            && Language::whereSlug($user->language)->count()
        ) {
            $lang = $user->language;
        }

        Translate::with(
            [
                'current' => function ($query) use ($lang) {
                    $query->setBindings(['language' => $lang]);
                }
            ]
        )
            ->get()
            ->map(
                function (Translate $translate) use (&$collection) {
                    if ($translate && isset($translate->current)) {
                        return $collection->put($translate->key, $translate->current->text);
                    }
                }
            );

        return TranslateResource::collection($collection);
    }
}
