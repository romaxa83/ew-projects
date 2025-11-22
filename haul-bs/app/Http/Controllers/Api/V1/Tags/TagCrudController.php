<?php

namespace App\Http\Controllers\Api\V1\Tags;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Tags\TagFilterRequest;
use App\Http\Requests\Tags\TagRequest;
use App\Http\Resources\Tags\TagListResource;
use App\Http\Resources\Tags\TagResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Models\Tags\Tag;
use App\Repositories\Tags\TagRepository;
use App\Services\Tags\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class TagCrudController extends ApiController
{
    public function __construct(
        protected TagRepository $repo,
        protected TagService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/tags",
     *     tags={"Tags"},
     *     security={{"Basic": {}}},
     *     summary="Get tags list",
     *     operationId="GetTagsList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="type", in="query", required=false,
     *         description="Filter by type",
     *         @OA\Schema(type="string", default="null", enum={"trucks_and_trailer", "customer"})
     *     ),
     *
     *     @OA\Response(response=200, description="Tag data",
     *         @OA\JsonContent(ref="#/components/schemas/TagListResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(TagFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Tag\TagReadPermission::KEY);

        return TagListResource::collection(
            $this->repo->list($request->validated())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tags",
     *     tags={"Tags"},
     *     security={{"Basic": {}}},
     *     summary="Create tag",
     *     operationId="CreateTags",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TagRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Tag data",
     *         @OA\JsonContent(ref="#/components/schemas/TagResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(TagRequest $request): TagResource|JsonResponse
    {
        $this->authorize(Permission\Tag\TagCreatePermission::KEY);

        if ($this->repo->countBy(['type' => $request->getDto()->type->value]) >= Tag::MAX_TAGS_COUNT_PER_TYPE) {
            return $this->errorJsonMessage(__('exceptions.tag.more_limit'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return TagResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/tags/{id}",
     *     tags={"Tags"},
     *     security={{"Basic": {}}},
     *     summary="Update tag",
     *     operationId="UpadteTags",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TagRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Tag data",
     *         @OA\JsonContent(ref="#/components/schemas/TagResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(TagRequest $request, $id): TagResource|JsonResponse
    {
        $this->authorize(Permission\Tag\TagUpdatePermission::KEY);

        if ($this->repo->countBy(['type' => $request->getDto()->type->value]) >= Tag::MAX_TAGS_COUNT_PER_TYPE) {
            return $this->errorJsonMessage(__('exceptions.tag.more_limit'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var $model Tag */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.tag.not_found")
        );

        return TagResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tags/{id}",
     *     tags={"Tags"},
     *     security={{"Basic": {}}},
     *     summary="Get info about tag",
     *     operationId="GetInfoAboutTag",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Tag data",
     *         @OA\JsonContent(ref="#/components/schemas/TagResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): TagResource
    {
        $this->authorize(Permission\Tag\TagReadPermission::KEY);

        return TagResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.tag.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tags/{id}",
     *     tags={"Tags"},
     *     security={{"Basic": {}}},
     *     summary="Delete tag",
     *     operationId="DeleteTag",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
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
    public function delete($id): JsonResponse
    {
        $this->authorize(Permission\Tag\TagDeletePermission::KEY);

        /** @var $model Tag */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.tag.not_found")
        );

        if ($model->hasRelatedEntities()) {
            return $this->errorJsonMessage($this->getMessageForDeleteFailed($model),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }

    protected function getMessageForDeleteFailed(Tag $model): string
    {
        if ($model->type->isCustomer()) {

            $link = str_replace('{id}', $model->id, config('routes.front.customers_with_tag_filter_url'));

            return __("exceptions.tag.used_customer", ['link' => $link]);
        }

        if ($model->type->isTrucksAndTrailer()) {
            if($model->trucks()->exists() && $model->trailers()->exists()){
                $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_tag_filter_url'));
                $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_tag_filter_url'));

                return __("exceptions.tag.has_truck_and_trailer", [
                    'trucks' => $truckLink,
                    'trailers' => $trailerLink
                ]);
            }
            if($model->trucks()->exists()){
                $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_tag_filter_url'));

                return __("exceptions.tag.has_truck", [
                    'trucks' => $truckLink,
                ]);
            }
            if($model->trailers()->exists()){
                $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_tag_filter_url'));

                return __("exceptions.tag.has_trailer", [
                    'trailers' => $trailerLink
                ]);
            }
        }

        return '';
    }
}
