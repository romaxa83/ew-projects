<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Dto\Catalog\CategoryDto;
use App\Dto\UpdateGuidDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Catalog\Categories\CategoryCreateRequest;
use App\Http\Requests\Api\OneC\Catalog\Categories\CategoryUpdateGuidRequest;
use App\Http\Requests\Api\OneC\Catalog\Categories\CategoryUpdateRequest;
use App\Http\Resources\Api\OneC\Catalog\Categories\CategoryResource;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories\CreatePermission;
use App\Permissions\Catalog\Categories\DeletePermission;
use App\Permissions\Catalog\Categories\ListPermission;
use App\Permissions\Catalog\Categories\UpdatePermission;
use App\Services\Catalog\Categories\CategoryService;
use App\Services\UpdateGuidService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

/**
 * @group Categories
 */
class CategoriesController extends Controller
{
    /**
     * List
     *
     * @permission catalog.category.list
     *
     * @responseFile docs/api/categories/list.json
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize(ListPermission::KEY);

        return CategoryResource::collection(
            Category::query()
                ->with('parent:id,guid')
                ->with('translations')
                ->get()
        );
    }

    /**
     * Show
     *
     * @permission catalog.category.list
     *
     * @responseFile docs/api/categories/single.json
     * @throws AuthorizationException
     */
    public function show(Category $category): CategoryResource
    {
        $this->authorize(ListPermission::KEY);

        $category->load(
            [
                'parent:id,guid',
                'translations'
            ]
        );

        return new CategoryResource($category);
    }

    /**
     * Store
     *
     * @permission catalog.category.create
     *
     * @responseFile 201 docs/api/categories/single.json
     *
     * @throws Throwable
     */
    public function store(CategoryCreateRequest $request, CategoryService $service): CategoryResource
    {
        $this->authorize(CreatePermission::KEY);

        $validated = $request->validated();

        return makeTransaction(
            function () use ($service, $validated) {
                $category = $service->create(
                    CategoryDto::byArgs($this->resolveArgs($validated))
                );

                return new CategoryResource($category);
            }
        );
    }

    protected function resolveArgs(array $args): array
    {
        $parent = $args['parent_guid'] ?? null;

        if ($parent) {
            $args['parent_id'] = Category::query()->where('guid', $parent)->first()->id;
        }

        return $args;
    }

    /**
     * Update
     *
     * @permission catalog.category.update
     *
     * @responseFile docs/api/categories/single.json
     *
     * @throws Throwable
     */
    public function update(
        Category $category,
        CategoryUpdateRequest $request,
        CategoryService $service
    ): CategoryResource {
        $this->authorize(UpdatePermission::KEY);

        return makeTransaction(
            static fn() => new CategoryResource(
                $service->update(
                    CategoryDto::byArgs($request->validated()),
                    $category,
                )
            )
        );
    }

    /**
     * Destroy
     *
     * @permission catalog.category.delete
     *
     * @response {
     * "success": true,
     * "message": "Category deleted"
     * }
     *
     * @throws Exception
     */
    public function destroy(string $guid, CategoryService $service): JsonResponse
    {
        $this->authorize(DeletePermission::KEY);

        $service->delete(Category::query()->where('guid', $guid)->firstOrFail());

        return $this->success('Category deleted');
    }

    /**
     * Update guid
     *
     * @permission catalog.category.update
     *
     * @responseFile docs/api/categories/list.json
     *
     * @throws Throwable
     */
    public function updateGuid(
        CategoryUpdateGuidRequest $request,
        UpdateGuidService $service
    ): AnonymousResourceCollection {
        $response = [];

        $ids = collect($request->get('data'))->pluck('id');
        $entities = Category::query()->whereKey($ids)->get();

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

        return CategoryResource::collection($response);
    }
}
