<?php

namespace App\Http\Controllers\Api\News;

use App\Http\Controllers\ApiController;
use App\Http\Requests\News\IndexNewsRequest;
use App\Http\Requests\News\NewsRequest;
use App\Http\Resources\News\NewsResource;
use App\Http\Resources\News\NewsResourceFull;
use App\Http\Resources\News\NewsPaginatedResource;
use App\Models\News\News;
use App\Services\Events\EventService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Throwable;

class NewsController extends ApiController
{
    /**
     * @param News $news
     * @param array $requestData
     * @throws Throwable
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function updateNewsRecord(News $news, array $requestData): void
    {
        $news->fill($requestData);
        $news->saveOrFail();

        if (isset($requestData[News::NEWS_PHOTO_FIELD_NAME])) {
            $news->addMediaWithRandomName(
                News::NEWS_PHOTO_COLLECTION_NAME,
                $requestData[News::NEWS_PHOTO_FIELD_NAME],
                true
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     *
     * @OA\Get(
     *     path="/api/news",
     *     tags={"News"},
     *     summary="Get news paginated list",
     *     operationId="Get news list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="News title",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="5"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Contacts per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Sort order",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum ={"asc","desc"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsPaginatedResource")
     *     ),
     * )
     * @param IndexNewsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexNewsRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $news = News::filter(
            Arr::only(
                $validated,
                [
                    'name',
                    'date_from',
                    'date_to'
                ]
            )
        );

        if (!$request->user()->can('news update')) {
            $news = $news->whereStatus(true);
        }

        $news = $news->orderBy('sticky', 'desc')
            ->orderBy('id', $validated['order_type'])
            ->paginate($validated['per_page']);

        return NewsPaginatedResource::collection($news);
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post(
     *     path="/api/news",
     *     tags={"News"},
     *     summary="Create news record",
     *     operationId="Create news",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="title_en",
     *          in="query",
     *          description="News title en",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="title_ru",
     *          in="query",
     *          description="News title ru",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="title_es",
     *          in="query",
     *          description="News title es",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_short_en",
     *          in="query",
     *          description="News body short en",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_short_ru",
     *          in="query",
     *          description="News body short ru",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_short_es",
     *          in="query",
     *          description="News body short es",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_en",
     *          in="query",
     *          description="News body en",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_ru",
     *          in="query",
     *          description="News body ru",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_es",
     *          in="query",
     *          description="News body es",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="sticky",
     *          in="query",
     *          description="News sticky",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="News status",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="image_file",
     *          in="query",
     *          description="News image",
     *          required=false,
     *          @OA\Schema(
     *              type="file",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsResourceFull")
     *     ),
     * )
     * @param NewsRequest $request
     * @return NewsResourceFull|JsonResponse
     * @throws Throwable
     */
    public function store(NewsRequest $request)
    {
        try {
            DB::beginTransaction();

            $news = new News();

            $this->updateNewsRecord($news, $request->validated());

            DB::commit();

            EventService::news($news)
                ->user($request->user())
                ->create()
                ->broadcast();

            return NewsResourceFull::make($news);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/news/{newsId}",
     *     tags={"News"},
     *     summary="Get news record",
     *     operationId="Get news record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="News id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsResource")
     *     ),
     * )
     * @param News $news
     * @return NewsResource
     * @throws AuthorizationException
     */
    public function show(News $news): NewsResource
    {
        $this->authorize('news read');

        return NewsResource::make($news);
    }

    /**
     * Display the specified resource.
     *
     *
     * @OA\Get(
     *     path="/api/news/{newsId}/full",
     *     tags={"News"},
     *     summary="Get news record full",
     *     operationId="Get news record full",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="News id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsResourceFull")
     *     ),
     * )
     * @param News $news
     * @return NewsResourceFull
     * @throws AuthorizationException
     */
    public function showFull(News $news): NewsResourceFull
    {
        $this->authorize('news update');

        return NewsResourceFull::make($news);
    }

    /**
     * Update the specified resource in storage.
     *
     *
     * @OA\Post(
     *     path="/api/news/{newsId}/update",
     *     tags={"News"},
     *     summary="Update news record",
     *     operationId="Update news",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="News id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="title_en",
     *          in="query",
     *          description="News title en",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="title_ru",
     *          in="query",
     *          description="News title ru",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="title_es",
     *          in="query",
     *          description="News title es",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_short_en",
     *          in="query",
     *          description="News body short en",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_short_ru",
     *          in="query",
     *          description="News body short ru",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_short_es",
     *          in="query",
     *          description="News body short es",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_en",
     *          in="query",
     *          description="News body en",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_ru",
     *          in="query",
     *          description="News body ru",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="body_es",
     *          in="query",
     *          description="News body es",
     *          required=false,
     *          @OA\Schema(
     *              type="text",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="sticky",
     *          in="query",
     *          description="News sticky",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="News status",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="image_file",
     *          in="query",
     *          description="News image",
     *          required=false,
     *          @OA\Schema(
     *              type="file",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsResourceFull")
     *     ),
     * )
     * @param NewsRequest $request
     * @param News $news
     * @return NewsResourceFull|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(NewsRequest $request, News $news)
    {
        $this->authorize('news update');

        try {
            DB::beginTransaction();

            $this->updateNewsRecord($news, $request->validated());

            DB::commit();

            EventService::news($news)
                ->user($request->user())
                ->update()
                ->broadcast();

            return NewsResourceFull::make($news);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/news/{newsId}",
     *     tags={"News"},
     *     summary="Delete news",
     *     operationId="Delete news",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="News id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param News $news
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(News $news): JsonResponse
    {
        $this->authorize('news delete');

        try {
            $news->delete();

            EventService::news($news)
                ->user(request()->user())
                ->delete()
                ->broadcast();

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/news/{newsId}/delete-photo",
     *     tags={"News"},
     *     summary="Delete news photo",
     *     operationId="Delete news photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="News id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsResourceFull")
     *     ),
     * )
     * @param News $news
     * @return NewsResourceFull|JsonResponse
     * @throws AuthorizationException
     */
    public function deletePhoto(News $news)
    {
        $this->authorize('news delete');

        try {
            $news->clearMediaCollection(News::NEWS_PHOTO_COLLECTION_NAME);

            EventService::news($news)
                ->user(request()->user())
                ->update()
                ->broadcast();

            return NewsResourceFull::make($news);
        } catch (Exception $e) {
            Log::error($e);
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Activate news.
     *
     * @OA\Put(
     *     path="/api/news/{newsId}/activate",
     *     tags={"News"},
     *     summary="Activate news record",
     *     operationId="Activate news record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="News id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsResourceFull")
     *     ),
     * )
     * @param News $news
     * @return NewsResourceFull|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function activate(News $news)
    {
        $this->authorize('news update');

        try {
            $news->status = true;
            $news->saveOrFail();

            EventService::news($news)
                ->user(request()->user())
                ->activate()
                ->broadcast();

            return NewsResourceFull::make($news);
        } catch (Exception $e) {
            Log::error($e);
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deactivate news.
     *
     * @OA\Put(
     *     path="/api/news/{newsId}/deactivate",
     *     tags={"News"},
     *     summary="Deactivate news record",
     *     operationId="Deactivate news record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="News id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewsResourceFull")
     *     ),
     * )
     * @param News $news
     * @return NewsResourceFull|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function deactivate(News $news)
    {
        $this->authorize('news update');

        try {
            $news->status = false;
            $news->saveOrFail();

            EventService::news($news)
                ->user(request()->user())
                ->deactivate()
                ->broadcast();

            return NewsResourceFull::make($news);
        } catch (Exception $e) {
            Log::error($e);
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
