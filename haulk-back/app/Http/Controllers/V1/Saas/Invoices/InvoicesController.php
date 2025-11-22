<?php

namespace App\Http\Controllers\V1\Saas\Invoices;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\Invoices\CompanyListRequest;
use App\Http\Requests\Saas\Invoices\InvoiceListRequest;
use App\Http\Resources\Billing\InvoicePaginatedResource;
use App\Http\Resources\Billing\InvoiceResource;
use App\Http\Resources\Saas\Invoices\CompanyListResource;
use App\Models\Billing\Invoice;
use App\Permissions\Saas\Invoices\InvoiceList;
use App\Permissions\Saas\Invoices\InvoiceShow;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoicesController extends ApiController
{

    /**
     * @param CompanyListRequest $request
     * @return AnonymousResourceCollection
     * @OA\Get(path="/v1/saas/invoices/companies-list",
     *     tags={"Saas Invoices"},
     *     summary="Returns company list",
     *     operationId="Comapny list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="company_name", in="query", description="Company name", required=false,
     *          @OA\Schema(type="string", minLength=2, maxLength=255)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceCompanyListResource")
     *     ),
     * )
     */
    public function companiesList(CompanyListRequest $request): AnonymousResourceCollection
    {
        $companyName = $request->get('company_name');
        $builder = $companyName === null ? Invoice::query() : Invoice::filter(['name' => $companyName]);

        $companies = $builder->groupBy('company_name')->selectRaw('MIN(carrier_id) AS carrier_id, company_name')->get();
        return CompanyListResource::collection($companies);
    }

    /**
     * @param InvoiceListRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/v1/saas/invoices",
     *     tags={"Saas Invoices"},
     *     summary="Returns invoices list",
     *     operationId="invoices list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="company_id", in="query", description="Company ID", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="dates_range", in="query", description="Billing period", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="payment_status", in="query", description="Pyment status", required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="all",
     *              enum={"all", "paid", "not_paid"}
     *          )
     *     ),
     *     @OA\Parameter(name="paid_dates_range", in="query", description="Paid period", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="asc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="order", in="query", description="Field to sort by", required=false,
     *          @OA\Schema(type="string", default="created_at", enum ={"company_name"})
     *     ),
     *     @OA\Parameter(name="has_gps_subscription", in="query", required=false,
     *           @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicePaginatedResource")
     *     ),
     * )
     */
    public function index(InvoiceListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(InvoiceList::KEY);

        $invoices = Invoice::withoutGlobalScopes()
            ->filter($request->validated())
            ->orderBy(
                $request->input('order', 'billing_start'),
                $request->input('order_type', 'desc')
            )
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return InvoicePaginatedResource::collection($invoices);
    }

    /**
     * @param Invoice $invoice
     * @return InvoiceResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/v1/saas/invoices/{invoiceId}",
     *     tags={"Saas Invoices"},
     *     summary="Returns invoice info",
     *     operationId="invoice info",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *         name="invoice",
     *         in="path",
     *         description="The ID of the invoice",
     *         required=true,
     *         @OA\Schema(
     *               type="integer",
     *               format="int64"
     *          ),
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceResource")
     *     ),
     * )
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize(InvoiceShow::KEY, $invoice);

        return InvoiceResource::make($invoice);
    }
}
