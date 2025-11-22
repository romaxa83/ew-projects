<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Dto\Catalog\Products\ProductDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Products\ProductCreateRequest;
use App\Http\Requests\Api\OneC\Products\ProductIndexRequest;
use App\Http\Requests\Api\OneC\Products\ProductUpdateGuidRequest;
use App\Http\Requests\Api\OneC\Products\ProductUpdateRequest;
use App\Http\Requests\BaseFormRequest;
use App\Http\Resources\Api\OneC\Products\ProductResource;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use App\Permissions\Catalog\Products\DeletePermission;
use App\Permissions\Catalog\Products\ListPermission;
use App\Services\Catalog\ProductService;
use App\Services\UpdateGuidService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use JsonException;
use Throwable;

/**
 * @group Products
 */
class ProductsController extends Controller
{
    /**
     * List
     *
     * @permission catalog.product.list
     *
     * @responseFile docs/api/products/list.json
     */
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()
                ->with('translations')
                ->with('certificates')
                ->with('category:id,guid')
                ->paginate($request->get('per_page'))
        );
    }

    /**
     * Show
     *
     * @permission catalog.product.list
     *
     * @responseFile docs/api/products/single.json
     * @throws AuthorizationException
     */
    public function show(Product $product): ProductResource
    {
        $this->authorize(ListPermission::KEY);

        $product->load('translations');

        return new ProductResource($product);
    }

    /**
     * Store
     *
     * @permission catalog.product.create
     *
     * @responseFile 201 docs/api/products/single.json
     *
     * @throws Throwable
     */
    public function store(ProductCreateRequest $request, ProductService $service): ProductResource
    {
        $dto = ProductDto::byArgs(
            $this->prepareArgs($request)
        );

        return makeTransaction(
            static fn() => new ProductResource(
                $service->create($dto)
            )
        );
    }

    /**
     * @throws JsonException
     */
    protected function prepareArgs(BaseFormRequest $request): array
    {
        $data = $request->validated();

        $data['category_id'] = Category::query()->where('guid', $data['category_guid'])->first()->id;

        $valueIds = [];

        if (!Arr::has($data, 'features')) {
            return $data;
        }

        $features = Feature::query()
            ->whereIn('guid', array_column($data['features'], 'guid'))
            ->select('id', 'guid')
            ->toBase()
            ->get()
            ->keyBy('guid');

        foreach ($data['features'] ?? [] as $f) {
            $feature = $features->get($f['guid']);

            if (!$feature) {
                continue;
            }

            $upsert = [];

            foreach ($f['values'] as $title) {
                $upsert[] = [
                    'feature_id' => $feature->id,
                    'title' => $title
                ];
            }

            Value::query()->upsert($upsert, ['feature_id', 'title']);

            $values = Value::query()
                ->where('feature_id', $feature->id)
                ->whereIn('title', $f['values'])
                ->select('id as value_id')
                ->toBase()
                ->get();

            $valueIds = array_merge(
                $valueIds,
                stdCollectionToArray($values)
            );
        }

        $data['features'] = $valueIds;

        return $data;
    }

    /**
     * Update
     *
     * @permission catalog.product.update
     *
     * @responseFile docs/api/products/single.json
     *
     * @throws Throwable
     */
    public function update(Product $product, ProductUpdateRequest $request, ProductService $service): ProductResource
    {
        $args = $this->prepareArgs($request);

        $dto = ProductDto::byArgs(
            $args,
            $request->isMethod('PATCH'),
            array_keys($args)
        );

        return makeTransaction(
            static fn() => new ProductResource(
                $service->update(
                    $dto,
                    $product,
                )
            )
        );
    }

    /**
     * Destroy
     *
     * @permission catalog.product.delete
     *
     * @response {
     * "success": true,
     * "data": [],
     * "message": "Product deleted"
     * }
     *
     * @throws Exception
     */
    public function destroy(Product $product, ProductService $service): JsonResponse
    {
        $this->authorize(DeletePermission::KEY);

        $service->remove($product);

        return $this->success('Product deleted');
    }

    /**
     * Update guid
     *
     * @permission catalog.product.update
     *
     * @responseFile docs/api/categories/update-guid.json
     *
     * @throws Throwable
     */
    public function updateGuid(
        ProductUpdateGuidRequest $request,
        UpdateGuidService $service
    ): AnonymousResourceCollection {
        return ProductResource::collection(
            $service->setGuids(
                $request->getDto(),
                Product::class
            )
        );
    }
}
