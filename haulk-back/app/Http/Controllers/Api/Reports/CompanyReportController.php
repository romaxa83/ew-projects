<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Reports\CompanyReportRequest;
use App\Http\Resources\Reports\CompanyReportResource;
use App\Http\Resources\Reports\CompanyReportTotalResource;
use App\Models\Orders\OrderOverdueData;
use App\Services\Orders\CompanySearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyReportController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param CompanyReportRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/v1/carrier/report/companies",
     *     tags={"Reports"},
     *     summary="Get accounts receivable report",
     *     operationId="Get accounts receivable report",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="payment_status",
     *          in="query",
     *          description="Include paid orders",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="all",
     *              enum={"all", "paid", "not_paid"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="invoice_from",
     *          in="query",
     *          description="Invoice date from",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="invoice_to",
     *          in="query",
     *          description="Invoice date to",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="company_name",
     *          in="query",
     *          description="Company name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="invoice_id",
     *          in="query",
     *          description="Invoice id",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="check_id",
     *          in="query",
     *          description="Uship or check number",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="payment_method_id",
     *          in="query",
     *          description="Payment method",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="1"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Orders per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="12"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field to sort by",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="id",
     *              enum={"company_name","total_count","latest_payment_date","total_due_count","total_due_count","past_due_count","total_due","past_due"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Sort order",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum={"asc","desc"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyReportResource")
     *     ),
     * )
     *
     */
    public function report(CompanyReportRequest $request, CompanySearchService $service): AnonymousResourceCollection
    {
        $dto = $request->dto();
        return CompanyReportResource::collection(
            $service->getCompanyReport(
                $dto->getFilter(),
                $dto->getPage(),
                $dto->getPerPage(),
                $dto->getOrderBy(),
                $dto->getOrderType()
            )
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param CompanyReportRequest $request
     * @return CompanyReportTotalResource
     * @OA\Get(
     *     path="/v1/carrier/report/comapnies-total",
     *     tags={"Reports"},
     *     summary="Get accounts receivable total",
     *     operationId="Get accounts receivable total",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyReportTotalResource")
     *     ),
     * )
     */
    public function reportTotal(CompanyReportRequest $request, CompanySearchService $service): CompanyReportTotalResource
    {
        $data = $service->getTotalCompanyReport($request->dto()->getFilter());

        return CompanyReportTotalResource::make([
            'total_due' => $data['total_due'],
            'current_due' => $data['current_due'],
            'past_due' => $data['past_due'],
        ]);
    }
}
