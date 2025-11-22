<?php

namespace App\Http\Controllers\Api\OneC\Orders\Categories;

use App\Dto\Orders\OrderCategoryDto;
use App\Dto\UpdateGuidDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Orders\Categories\OrderCategoryCreateRequest;
use App\Http\Requests\Api\OneC\Orders\Categories\OrderCategoryListRequest;
use App\Http\Requests\Api\OneC\Orders\Categories\OrderCategoryUpdateGuidRequest;
use App\Http\Requests\Api\OneC\Orders\Categories\OrderCategoryUpdateRequest;
use App\Http\Resources\Api\OneC\Orders\OrderCategories\OrderCategoryResource;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryDeletePermission;
use App\Services\Orders\OrderCategoryService;
use App\Services\UpdateGuidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

/**
 * @group Order Parts
 */
class OrderCategoryController extends Controller
{
    /**
     * List
     *
     * @permission order.category.list
     *
     * @responseFile docs/api/orders/categories/list.json
     */
    public function index(OrderCategoryListRequest $request): AnonymousResourceCollection
    {
        $values = $request->validated();

        if (!array_key_exists('published', $values)) {
            $values['published'] = true;
        }

        return OrderCategoryResource::collection(
            OrderCategory::query()
                ->filter($values)
                ->with('translations')
                ->get()
        );
    }

    /**
     * Store
     *
     * @permission order.category.create
     *
     * @responseFile 201 docs/api/orders/categories/single.json
     *
     * @throws Throwable
     */
    public function store(OrderCategoryCreateRequest $request, OrderCategoryService $service): OrderCategoryResource
    {
        return makeTransaction(
            static fn() => new OrderCategoryResource(
                $service->create(
                    OrderCategoryDto::byArgs($request->validated())
                )
            )
        );
    }

    /**
     * Destroy
     *
     * @permission order.category.delete
     *
     * @response {
     * "success": true,
     * "message": "Order part deleted"
     * }
     *
     * @throws Throwable
     */
    public function destroy(
        OrderCategory $orderPart,
        OrderCategoryService $service
    ): JsonResponse {
        $this->authorize(OrderCategoryDeletePermission::KEY);

        $service->deleteModel($orderPart);

        return $this->success('Order part deleted');
    }

    /**
     * Update
     *
     * @permission order.category.update
     *
     * @responseFile docs/api/orders/categories/single.json
     *
     * @throws Throwable
     */
    public function update(
        OrderCategory $orderPart,
        OrderCategoryUpdateRequest $request,
        OrderCategoryService $service
    ): OrderCategoryResource {
        return makeTransaction(
            static fn() => new OrderCategoryResource(
                $service->updateByModel(
                    $orderPart,
                    OrderCategoryDto::byArgs($request->validated()),
                )
            )
        );
    }

    /**
     * Update guid
     *
     * @permission order.category.update
     *
     * @responseFile docs/api/products/update-guid.json
     *
     * @throws Throwable
     */
    public function updateGuid(
        OrderCategoryUpdateGuidRequest $request,
        UpdateGuidService $service
    ): AnonymousResourceCollection {
        $response = [];

        $ids = collect($request->get('data'))->pluck('id');
        $entities = OrderCategory::query()->whereKey($ids)->get();

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

        return OrderCategoryResource::collection($response);
    }
}
