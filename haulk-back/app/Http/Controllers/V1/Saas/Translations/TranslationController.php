<?php


namespace App\Http\Controllers\V1\Saas\Translations;


use App\Http\Controllers\ApiController;
use App\Http\Requests\Translates\TranslateRequest;
use App\Http\Requests\Translates\TranslateSyncRequest;
use App\Http\Resources\Translates\TranslateMultiLangResource;
use App\Models\Translates\Translate;
use App\Permissions\Saas\Translations\TranslationCreate;
use App\Permissions\Saas\Translations\TranslationDelete;
use App\Permissions\Saas\Translations\TranslationList;
use App\Permissions\Saas\Translations\TranslationShow;
use App\Permissions\Saas\Translations\TranslationUpdate;
use App\Services\Translates\TranslateService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Throwable;

class TranslationController extends ApiController
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/v1/saas/translates",
     *     tags={"V1 Saas Translates"},
     *     summary="Get translates paginated list",
     *     operationId="Get translates data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Translates per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field to sort by", required=false,
     *          @OA\Schema(type="string", default="id", enum ={"id","full_name"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Sort order", required=false,
     *          @OA\Schema(type="string", default="asc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="text", in="query", description="Scope for filter by text", required=false,
     *          @OA\Schema(type="string", default="Text")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TranslatePaginate")
     *     ),
     * )
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize(TranslationList::KEY);

        $orderBy = in_array($request->input('order_by'), ['id', 'key']) ? $request->input('order_by') : 'id';
        $orderByType = in_array($request->input('order_type'), ['asc', 'desc']) ? $request->input('order_type') : 'asc';
        $perPage = (int) $request->input('per_page', 10);

        return TranslateMultiLangResource::collection(
            Translate::query()
                ->with('data')
                ->filter($request->only('text'))
                ->orderBy($orderBy, $orderByType)
                ->paginate($perPage)
        );
    }

    /**
     * @param TranslateRequest $request
     * @param Translate $translate
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     * @OA\Post(
     *     path="/v1/saas/translates",
     *     tags={"V1 Saas Translates"},
     *     summary="Create translate",
     *     operationId="Create translate",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="key", in="query", description="Translate key", required=true,
     *          @OA\Schema(type="string", default="common/key/etc")
     *     ),
     *     @OA\Parameter(name="language_slug['text']", in="query",
     *          description="Language slug es,en,ru etc + text of translate = en['text']",
     *          required=false,
     *          @OA\Schema(type="string", default="text translate")
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TranslateMultiLang")
     *     ),
     * )
     */
    public function store(TranslateRequest $request, Translate $translate)
    {
        $this->authorize(TranslationCreate::KEY);

        if ($translate->createRow($request)) {
            return TranslateMultiLangResource::make($translate)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param Translate $translate
     * @return TranslateMultiLangResource
     *
     * @OA\Get(
     *     path="/v1/saas/translates/{translateId}",
     *     tags={"V1 Saas Translates"},
     *     summary="Get info about translate",
     *     operationId="Get translate data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TranslateMultiLang")
     *     ),
     * )
     * @throws AuthorizationException
     */
    public function show(Translate $translate)
    {
        $this->authorize(TranslationShow::KEY);

        return new TranslateMultiLangResource($translate);
    }

    /**
     * @param TranslateRequest $request
     * @param Translate $translate
     * @return TranslateMultiLangResource|JsonResponse
     * @throws Exception
     *
     * @OA\Put(
     *     path="/v1/saas/translates/{translateId}",
     *     tags={"V1 Saas Translates"},
     *     summary="Update translate",
     *     operationId="Update translate",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="id", in="path", description="Translate id", required=true,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="key", in="query", description="Translate key", required=true,
     *          @OA\Schema(type="string", default="common/key/etc")
     *     ),
     *     @OA\Parameter(
     *          name="language_slug['text']",
     *          in="query",
     *          description="Language slug es,en,ru etc + text of translate = en['text']",
     *          required=false,
     *          @OA\Schema(type="string", default="text translate")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TranslateMultiLang")
     *     ),
     * )
     */
    public function update(TranslateRequest $request, Translate $translate)
    {
        $this->authorize(TranslationUpdate::KEY);

        if ($translate->updateRow($request)) {
            return TranslateMultiLangResource::make($translate);
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param Translate $translate
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/v1/saas/translates/{translateId}",
     *     tags={"V1 Saas Translates"},
     *     summary="Delete translate",
     *     operationId="Delete translate",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     * @throws AuthorizationException
     */
    public function destroy(Translate $translate)
    {
        $this->authorize(TranslationDelete::KEY);

        if ($translate->deleteRow()) {
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     *
     * @OA\Post(
     *     path="/v1/saas/translates/sync",
     *     tags={"V1 Saas Translates"},
     *     summary="Batch sync translates",
     *     operationId="Batch sync translates",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(name="translates", in="query", description="Translates", required=true,
     *          @OA\Schema(type="array", description="Translates",
     *              @OA\Items(
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Parameter(name="key", in="query", description="Translate key", required=true,
     *                              @OA\Schema(type="string", default="common/key/etc")
     *                          ),
     *                          @OA\Parameter(
     *                              name="language_slug['text']",
     *                              in="query",
     *                              description="Language slug es,en,ru etc + text of translate = en['text']",
     *                              required=false,
     *                              @OA\Schema(type="string", default="text translate")
     *                          ),
     *                      )
     *                  }
     *              )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     *
     * @param TranslateSyncRequest $request
     * @param TranslateService $service
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function sync(TranslateSyncRequest $request, TranslateService $service)
    {
        $this->authorize(TranslationUpdate::KEY);

        try {
            DB::beginTransaction();

            $service->clear();

            $service->insert($request->translates);
            DB::commit();

            return $this->makeSuccessResponse(null, Response::HTTP_OK);
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error($exception);

            return $this->makeErrorResponse('Translates sync fail!', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
