<?php

namespace App\Http\Controllers\Api\BodyShop\Suppliers;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Suppliers\SupplierIndexRequest;
use App\Http\Requests\BodyShop\Suppliers\SupplierRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\BodyShop\Suppliers\SupplierPaginateResource;
use App\Http\Resources\BodyShop\Suppliers\SupplierResource;
use App\Http\Resources\BodyShop\Suppliers\SupplierShortListResource;
use App\Models\BodyShop\Suppliers\Supplier;
use App\Services\BodyShop\Suppliers\SupplierService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class SupplierController extends ApiController
{
    protected SupplierService $service;

    public function __construct(SupplierService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param SupplierIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/suppliers",
     *     tags={"Suppliers"},
     *     summary="Get suppliers paginated list",
     *     operationId="Get suppliers data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
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
     *          description="States per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="Scope for filter by name, email, phone",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="name",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierPaginate")
     *     ),
     * )
     */
    public function index(SupplierIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('suppliers');

        $suppliers = Supplier::query()
            ->filter($request->validated())
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return SupplierPaginateResource::collection($suppliers);
    }

    /**
     * @param SupplierRequest $request
     * @return SupplierResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/suppliers", tags={"Suppliers"}, summary="Create Supplier", operationId="Create Supplier", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Supplier name", required=true,
     *          @OA\Schema(type="string", default="Mike Stone",)
     *     ),
     *     @OA\Parameter(name="url", in="query", description="Supplier url", required=false,
     *          @OA\Schema(type="string", default="https://wezom.com",)
     *     ),
     *     @OA\Parameter(name="contacts", in="query", description="Supplier contacts list", required=true,
     *          @OA\Schema(type="array",
     *              @OA\Items(allOf={@OA\Schema(ref="#/components/schemas/SupplierContact")})
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     * )
     */
    public function store(SupplierRequest $request)
    {
        $this->authorize('suppliers create');

        try {
            $supplier = $this->service->create($request->dto());

            return SupplierResource::make($supplier);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/suppliers/{supplierId}",
     *     tags={"Suppliers"},
     *     summary="Get supplier record",
     *     operationId="Get supplier record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Supplier id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     * )
     * @param Supplier $supplier
     * @return SupplierResource
     * @throws AuthorizationException
     */
    public function show(Supplier $supplier): SupplierResource
    {
        $this->authorize('suppliers read');

        return SupplierResource::make($supplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/body-shop/suppliers/{supplierId}",
     *     tags={"Suppliers"},
     *     summary="Update supplier record",
     *     operationId="Update supplier",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Supplier id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="name", in="query", description="Supplier name", required=true,
     *          @OA\Schema(type="string", default="Mike Stone",)
     *     ),
     *     @OA\Parameter(name="url", in="query", description="Supplier url", required=false,
     *          @OA\Schema(type="string", default="https://wezom.com",)
     *     ),
     *     @OA\Parameter(name="contacts", in="query", description="Supplier contacts list", required=true,
     *          @OA\Schema(type="array",
     *              @OA\Items(allOf={@OA\Schema(ref="#/components/schemas/SupplierContact")})
     *          )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     * )
     * @param SupplierRequest $request
     * @param Supplier $supplier
     * @return SupplierResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $this->authorize('suppliers update');

        try {
            $this->service->update($supplier, $request->dto());

            return SupplierResource::make($supplier);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/body-shop/suppliers/{supplierId}",
     *     tags={"Suppliers"},
     *     summary="Delete supplier",
     *     operationId="Delete supplier",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Supplier id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param Supplier $supplier
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->authorize('suppliers delete');

        try {
            $this->service->destroy($supplier);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $exception) {
            return $this->makeErrorResponse(
                trans(
                    'Supplier has inventory assigned. Please check the list of inventory. <a href=":link">Check inventory list</a>',
                    [
                        'link' => str_replace('{id}', $supplier->id, config('frontend.bs_inventories_with_supplier_filter_url'))
                    ]
                ),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/suppliers/shortlist",
     *     tags={"Suppliers Body Shop"},
     *     summary="Get Suppliers short list",
     *     operationId="Get Suppliers data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="searchid", in="query", description="Filter by id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierShortList"),
     *     )
     * )
     */
    public function shortlist(SearchRequest $request): AnonymousResourceCollection
    {
        $suppliers = Supplier::query()
            ->filter($request->validated())
            ->limit(SearchRequest::DEFAULT_LIMIT)
            ->get();

        return SupplierShortListResource::collection($suppliers);
    }
}
