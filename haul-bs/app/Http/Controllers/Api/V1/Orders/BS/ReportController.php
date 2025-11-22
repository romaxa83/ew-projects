<?php

namespace App\Http\Controllers\Api\V1\Orders\BS;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\BS\OrderReportRequest;
use App\Http\Resources\Orders\BS\OrderReportPaginationResource;
use App\Http\Resources\Orders\BS\OrderReportTotalResource;
use App\Repositories\Orders\BS\OrderRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/bs/report",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Get bodyshop orders report data",
     *     operationId="GetBSOrderReportData",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(ref="#/components/parameters/OrderType"),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *         @OA\Schema(type="string", default="status", enum ={"current_due", "past_due", "total_due"})
     *     ),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for search by vin, order number", required=false,
     *         @OA\Schema(type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="statuses", in="query", description="Order status", required=false,
     *         @OA\Schema(type="array",
     *             @OA\Items(allOf={@OA\Schema(type="string", enum={"new","in_process","finished"})})
     *         )
     *     ),
     *     @OA\Parameter(name="payment_statuses", in="query", description="Order payment status", required=false,
     *         @OA\Schema(type="array",
     *             @OA\Items(allOf={@OA\Schema(type="string", enum={"paid","not_paid","billed","not_billed","overdue","not_overdue"})})
     *         )
     *     ),
     *     @OA\Parameter(name="implementation_date_from", in="query", description="Order implementation date from", required=false,
     *         @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="implementation_date_to", in="query", description="Order implementation date to", required=false,
     *         @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop report data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSReportPagination")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(OrderReportRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Order\BS\OrderReadPermission::KEY);

        return OrderReportPaginationResource::collection(
            $this->repo->customReportPagination($request->validated())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/bs/report-total",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Get bodyshop orders report total data",
     *     operationId="GetBSOrderReportTotalData",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for search by vin, order number", required=false,
     *         @OA\Schema(type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="statuses", in="query", description="Order status", required=false,
     *         @OA\Schema(type="array",
     *             @OA\Items(allOf={@OA\Schema(type="string", enum={"new","in_process","finished"})})
     *         )
     *     ),
     *     @OA\Parameter(name="payment_statuses", in="query", description="Order payment status", required=false,
     *         @OA\Schema(type="array",
     *             @OA\Items(allOf={@OA\Schema(type="string", enum={"paid","not_paid","billed","not_billed","overdue","not_overdue"})})
     *         )
     *     ),
     *     @OA\Parameter(name="implementation_date_from", in="query", description="Order implementation date from", required=false,
     *         @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="implementation_date_to", in="query", description="Order implementation date to", required=false,
     *         @OA\Schema(type="string", default="2023-02-13 10:00",)
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop report total data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSReportTotal")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function total(OrderReportRequest $request): OrderReportTotalResource
    {
        $this->authorize(Permission\Order\BS\OrderReadPermission::KEY);

        return OrderReportTotalResource::make(
            $this->repo->orderTotalData($request->validated())
        );
    }
}
