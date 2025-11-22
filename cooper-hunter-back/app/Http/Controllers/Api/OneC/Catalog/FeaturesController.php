<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Dto\UpdateGuidDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Catalog\Features\FeatureRequest;
use App\Http\Requests\Api\OneC\Catalog\Features\FeaturesListRequest;
use App\Http\Requests\Api\OneC\Catalog\Features\FeatureUpdateGuidRequest;
use App\Http\Resources\Api\OneC\Catalog\Features\FeatureResource;
use App\Models\Catalog\Features\Feature;
use App\Permissions\Catalog\Features\Features\CreatePermission;
use App\Permissions\Catalog\Features\Features\DeletePermission;
use App\Permissions\Catalog\Features\Features\ListPermission;
use App\Permissions\Catalog\Features\Features\UpdatePermission;
use App\Services\Catalog\FeatureService;
use App\Services\UpdateGuidService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @group Features
 */
class FeaturesController extends Controller
{
    /**
     * List
     *
     * @permission catalog.feature.feature.list
     *
     * @responseFile docs/api/features/list.json
     *
     * @throws AuthorizationException
     */
    public function index(FeaturesListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(ListPermission::KEY);

        return FeatureResource::collection(
            Feature::query()
                ->filter($request->validated())
                ->with('translations')
                ->with('values')
                ->latest('sort')
                ->paginate()
        );
    }

    /**
     * Show
     *
     * @permission catalog.feature.feature.list
     *
     * @responseFile docs/api/features/single.json
     *
     * @throws AuthorizationException
     */
    public function show(Feature $feature): FeatureResource
    {
        $this->authorize(ListPermission::KEY);

        $feature->loadMissing(['translations', 'values']);

        return FeatureResource::make($feature);
    }

    /**
     * Store
     *
     * @permission catalog.feature.feature.create
     *
     * @responseFile 201 docs/api/features/single.no.values.json
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(FeatureRequest $request, FeatureService $service): JsonResponse
    {
        $this->authorize(CreatePermission::KEY);

        return makeTransaction(
            static fn() => FeatureResource::make(
                $service->create(
                    $request->getDto()
                )
            )
                ->response()
                ->setStatusCode(Response::HTTP_CREATED)
        );
    }

    /**
     * Update
     *
     * @permission catalog.feature.feature.update
     *
     * @responseFile docs/api/features/single.json
     *
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function update(Feature $feature, FeatureRequest $request, FeatureService $service): FeatureResource
    {
        $this->authorize(UpdatePermission::KEY);

        return makeTransaction(
            static fn() => FeatureResource::make(
                $service->update(
                    $request->getDto(),
                    $feature
                )
            )
        );
    }

    /**
     * Destroy
     *
     * @permission catalog.feature.feature.delete
     *
     * @response {
     * "success": true,
     * "message": "Feature deleted"
     * }
     *
     * @throws AuthorizationException
     * @throws Exception
     */
    public function destroy(Feature $feature, FeatureService $service): JsonResponse
    {
        $this->authorize(DeletePermission::KEY);

        $service->delete($feature);

        return $this->success('Feature deleted');
    }

    /**
     * Update guid
     *
     * @permission catalog.feature.feature.update
     *
     * @responseFile docs/api/features/list.json
     *
     * @throws Throwable
     */
    public function updateGuid(
        FeatureUpdateGuidRequest $request,
        UpdateGuidService $service
    ): AnonymousResourceCollection {
        $response = [];

        $ids = collect($request->get('data'))->pluck('id');
        $entities = Feature::query()->whereKey($ids)->get();

        foreach ($request->get('data') as $userData) {
            $response[] = makeTransaction(
                static function () use ($service, $userData, $entities) {
                    $dto = UpdateGuidDto::byArgs($userData);

                    return $service->updateGuid(
                        $entities->where('id', $dto->getId())->first(),
                        $dto
                    );
                }
            );
        }

        return FeatureResource::collection($response);
    }
}
