<?php

namespace App\Http\Controllers\Api\V1\Inventories\Inventory;

use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Requests\Inventories\Inventory\InventoryFilterRequest;
use App\Repositories\Inventories\InventoryRepository;
use App\Services\Inventories\InventoryService;
use Illuminate\Http\JsonResponse;

class ExportController extends ApiController
{
    public function __construct(
        protected InventoryRepository $repo,
        protected InventoryService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/export",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Returns a link to download excel file",
     *     operationId="InventoriesExcelFile",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name and stock_number",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", description="Category id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="brand_id", in="query", description="Brand id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="supplier_id", in="query", description="Supplier id", required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Status", required=false,
     *         @OA\Schema(type="string", example="in_stock", enum={"in_stock", "out_of_stock"})
     *     ),
     *     @OA\Parameter(name="only_min_limit", in="query", description="Resc only min limit", required=false,
     *         @OA\Schema(type="bool", default="true")
     *     ),
     *     @OA\Parameter(name="for_shop", in="query", description="For shop", required=false,
     *         @OA\Schema(type="bool", default="true")
     *     ),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function export(InventoryFilterRequest $request): JsonResponse
    {
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        return $this->successJsonMessage(
            $this->service->linkExcelExport(
                $this->repo->dataForExport(
                    filters: $request->validated()
                )
            )
        );
    }
}
