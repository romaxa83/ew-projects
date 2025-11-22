<?php

namespace App\Http\Controllers\Api\Billing;

use App\Exceptions\Billing\TransactionUnderReviewException;
use App\Http\Controllers\ApiController;
use App\Http\Resources\Billing\InvoicePaginatedResource;
use App\Http\Resources\Billing\InvoiceResource;
use App\Models\Billing\Invoice;
use App\Models\Orders\Order;
use App\Notifications\Saas\Invoices\InvoicePaymentPending;
use App\Scopes\CompanyScope;
use App\Services\Billing\BillingService;
use App\Services\BodyShop\Settings\SettingsService;
use App\Services\Events\EventService;
use App\Services\Orders\GeneratePdfService;
use App\Services\Saas\BackofficeService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Throwable;

class InvoiceController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('billing');

        $orderBy = 'billing_end';
        $orderByType = in_array($request->input('order_type'), ['asc', 'desc']) ? $request->input('order_type') : 'desc';
        $perPage = (int) $request->input('per_page', 10);

        $invoices = Invoice::filter($request->only([
            'dates_range',
            'is_paid',
            'attempt',
        ]))
            ->orderBy($orderBy, $orderByType)
            ->paginate($perPage);

        return InvoicePaginatedResource::collection($invoices);
    }

    /**
     * Display the specified resource.
     *
     * @param Invoice $invoice
     * @return InvoiceResource
     * @throws AuthorizationException
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('billing');

        return InvoiceResource::make($invoice);
    }

    /**
     * @param Request $request
     * @return string|null
     * @throws Throwable
     */
    public function getPdf(Request $request)
    {
        if (!$request->public_token) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        /** @var $invoice Invoice */
        $invoice = Invoice::withoutGlobalScope(CompanyScope::class)
            ->where('public_token', $request->public_token)
            ->first();

        if (!$invoice) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        /** @var $pdfService GeneratePdfService */
        $pdfService = resolve(GeneratePdfService::class);

        return $pdfService->template2pdf(
            [
                'pdf.billing.invoice',
                'pdf.billing.invoice-terms'
            ],
            [
                'invoice' => $invoice,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Invoice $invoice
     * @param BillingService $billingService
     * @param BackofficeService $backofficeService
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function payInvoice(Request $request, Invoice $invoice, BillingService $billingService, BackofficeService $backofficeService): JsonResponse
    {
        $this->authorize('billing');

        $company = $request->user()->getCompany();

        if (!$company->hasPaymentMethod() || $invoice->is_paid) {
            return $this->makeErrorResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $billingService->chargeInvoice($invoice);

            EventService::billing($company)
                ->update()
                ->broadcast();

            return $this->makeSuccessResponse();
        } catch (TransactionUnderReviewException $e) {
            $billingService->markInvoicePaymentPending($invoice, $e->getTransID());

            Notification::route(
                'mail',
                $backofficeService->getSuperAdmin()->email
            )->notify(
                new InvoicePaymentPending(
                    $e->getTransID()
                )
            );

            return $this->makeErrorResponse(
                trans(TransactionUnderReviewException::MESSAGE),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}

/**
 *
 * @OA\Put(
 *     path="/api/billing/invoices/{invoiceId}/pay",
 *     tags={"Billing - Invoices"},
 *     summary="Pay Invoice",
 *     operationId="Pay Invoice",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=200, description="Successful operation",),
 * )
 *
 * @OA\Get(
 *     path="/api/billing/invoices",
 *     tags={"Billing - Invoices"},
 *     summary="Get Invoices paginated list",
 *     operationId="Get Invoices data",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
 *          @OA\Schema(type="integer", default="5")
 *     ),
 *     @OA\Parameter(name="per_page", in="query", description="Contacts per page", required=false,
 *          @OA\Schema(type="integer", default="10")
 *     ),
 *     @OA\Parameter(name="order_by", in="query", description="Field to sort by", required=false,
 *          @OA\Schema(type="string", default="billing_end")
 *     ),
 *     @OA\Parameter(name="order_type", in="query", description="Sort order", required=false,
 *          @OA\Schema(type="string", default="asc", enum ={"asc","desc"})
 *     ),
 *     @OA\Parameter(name="is_paid", in="query", required=false,
 *          @OA\Schema(type="boolean", )
 *     ),
 *     @OA\Parameter(name="attempt", in="query", required=false,
 *           @OA\Schema(type="integer", description="It is possible to pass both a single value and an array")
 *     ),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/InvoicePaginatedResource")
 *     ),
 * )
 *
 * @OA\Get(
 *     path="/api/billing/invoices/{invoiceId}",
 *     tags={"Billing - Invoices"},
 *     summary="Get Invoice info",
 *     operationId="Get Invoice data",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/InvoiceResource")
 *     ),
 * )
 *
 * @OA\Get(
 *     path="/api/billing/invoices/{invoiceId}/invoice.pdf",
 *     tags={"Billing - Invoices"},
 *     summary="Get Invoice info in pdf",
 *     operationId="Get Invoice data in pdf",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=200, description="Successful operation",),
 * )
 *
 */
