<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Catalog\Features\ValueRequest;
use App\Http\Requests\Api\OneC\Catalog\Features\ValueUpdateRequest;
use App\Http\Resources\Api\OneC\Catalog\Features\ValueResource;
use App\Models\Catalog\Features\Value;
use App\Permissions\Catalog\Features\Values\CreatePermission;
use App\Permissions\Catalog\Features\Values\DeletePermission;
use App\Permissions\Catalog\Features\Values\UpdatePermission;
use App\Services\Catalog\ValueService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @group FeatureValues
 */
class ValuesController extends Controller
{
    /**
     * Store
     *
     * @permission catalog.feature.value.create
     *
     * @responseFile 201 docs/api/features/value.json
     *
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function store(ValueRequest $request, ValueService $service): JsonResponse
    {
        $this->authorize(CreatePermission::KEY);

        return makeTransaction(
            static fn() => ValueResource::make(
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
     * @permission catalog.feature.value.update
     *
     * @responseFile docs/api/features/value.json
     *
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function update(Value $value, ValueUpdateRequest $request, ValueService $service): ValueResource
    {
        $this->authorize(UpdatePermission::KEY);

        return makeTransaction(
            static fn() => ValueResource::make(
                $service->update(
                    $request->getDto(),
                    $value
                )
            )
        );
    }

    /**
     * Destroy
     *
     * @permission catalog.feature.value.delete
     *
     * @response {
     * "success": true,
     * "message": "Value deleted"
     * }
     *
     * @throws AuthorizationException
     * @throws Exception
     */
    public function destroy(Value $value, ValueService $service): JsonResponse
    {
        $this->authorize(DeletePermission::KEY);

        $service->delete($value);

        return $this->success('Value deleted');
    }
}
