<?php

namespace App\Http\Controllers\Api\Tags;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Tags\IndexTagRequest;
use App\Http\Requests\Tags\TagRequest;
use App\Http\Resources\Tags\TagListResource;
use App\Http\Resources\Tags\TagResource;
use App\Models\Tags\Tag;
use App\Services\Tags\TagService;
use App\Exceptions\Tag\MaxCountReachedException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Throwable;

class TagController extends ApiController
{
    protected array $types = Tag::TYPES_CRM;

    protected TagService $service;

    public function __construct(TagService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param IndexTagRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/tags",
     *     tags={"Tags"},
     *     summary="Get tags list",
     *     operationId="Get Tags data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="Scope for filter by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="name",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TagList"),
     *     )
     * )
     */
    public function index(IndexTagRequest $request): AnonymousResourceCollection
    {
        $this->authorize('tags');

        $tags = $this->service->getList($request->validated(), $this->types);

        return TagListResource::collection($tags);
    }

    /**
     * @param TagRequest $request
     * @return TagResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/tags", tags={"Tags"}, summary="Create tag", operationId="Create tag", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Tag name", required=true,
     *          @OA\Schema(type="string", default="Tag1",)
     *     ),
     *     @OA\Parameter(name="color", in="query", description="Tag color", required=true,
     *          @OA\Schema(type="string", default="#ffffff",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Tag type", required=true,
     *          @OA\Schema(type="string", default="order", enum={"order, trucks_and_trailer, vehicle_owner"})
     *     ),
     *
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     * )
     */
    public function store(TagRequest $request)
    {
        $this->authorize('tags create');

        try {
            $tag = $this->service->create($request->validated());

            return TagResource::make($tag);
        } catch (MaxCountReachedException $exception) {
            return $this->makeErrorResponse(trans('Max count of tags reached'), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Tag $tag
     * @return TagResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/tags/{tagId}",
     *     tags={"Tags"}, summary="Get tag data", operationId="Get tag data", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     * )
     */
    public function show(Tag $tag): TagResource
    {
        $this->authorize('tags read');

        return TagResource::make($tag);
    }

    /**
     * @param TagRequest $request
     * @param Tag $tag
     * @return TagResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/tags/{tagId}", tags={"Tags"}, summary="Update tag", operationId="Update tag", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Tag id", required=true,
     *          @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="name", in="query", description="Tag name", required=true,
     *          @OA\Schema(type="string", default="Tag1",)
     *     ),
     *     @OA\Parameter(name="color", in="query", description="Tag color", required=true,
     *          @OA\Schema(type="string", default="#ffffff",)
     *     ),
     *     @OA\Parameter(name="type", in="query", description="Tag type", required=true,
     *          @OA\Schema(type="string", default="order", enum={"order, trucks_and_trailer, vehicle_owner"})
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     * )
     */
    public function update(TagRequest $request, Tag $tag)
    {
        $this->authorize('tags update');

        try {
            $tag = $this->service->update($tag, $request->validated());

            return TagResource::make($tag);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Tag $tag
     * @return JsonResponse
     * @OA\Delete(
     *     path="/api/tags/{tagId}",
     *     tags={"Tags"}, summary="Delete tag", operationId="Delete tag", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation",),
     * )
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $this->authorize('tags delete');

        try {
            $this->service->destroy($tag);
        } catch (HasRelatedEntitiesException $exception) {
            return $this->makeErrorResponse(
                $this->getMessageForDestroyFailed($tag),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    protected function getMessageForDestroyFailed(Tag $tag): string
    {
        if ($tag->type === Tag::TYPE_ORDER) {
            return trans(
                'This tag is already used for <a href=":link">orders</a>',
                [
                    'link' => str_replace('{id}', $tag->id, config('frontend.orders_with_tag_filter_url'))
                ]
            );
        }

        if ($tag->type === Tag::TYPE_TRUCKS_AND_TRAILER) {
            if ($tag->trucks()->exists() && $tag->trailers()->exists()) {
                return trans(
                    'This tag is already used for  <a href=":trucks">trucks</a> and  <a href=":trailers">trailers</a>',
                    [
                        'trucks' => str_replace('{id}', $tag->id, config('frontend.trucks_with_tag_filter_url')),
                        'trailers' => str_replace('{id}', $tag->id, config('frontend.trailers_with_tag_filter_url')),
                    ],
                );
            }

            if ($tag->trucks()->exists()) {
                return trans(
                    'This tag is already used for  <a href=":trucks">trucks</a>',
                    [
                        'trucks' => str_replace('{id}', $tag->id, config('frontend.trucks_with_tag_filter_url')),
                    ],
                );
            }

            if ($tag->trailers()->exists()) {
                return trans(
                    'This tag is already used for  <a href=":trailers">trailers</a>',
                    [
                        'trailers' => str_replace('{id}', $tag->id, config('frontend.trailers_with_tag_filter_url')),
                    ],
                );
            }
        }

        if ($tag->type === Tag::TYPE_VEHICLE_OWNER) {
            return trans(
                'This tag is already used for <a href=":link">vehicle owners</a>',
                [
                    'link' => str_replace('{id}', $tag->id, config('frontend.users_with_tag_filter_url'))
                ]
            );
        }

        return '';
    }
}
