<?php

namespace App\Http\Controllers\Api\V1\Orders\BS;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\BS\OrderGenerateInvoiceRequest;
use App\Http\Requests\Orders\BS\OrderSendDocsRequest;
use App\Models\Orders\BS\Order;
use App\Repositories\Orders\BS\OrderRepository;
use App\Services\Orders\BS\InvoiceService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected InvoiceService $serviceInvoice,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/bs/{id}/generate-invoice",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Get Invoice pdf file for bs order",
     *     operationId="GetInvoicePdfFileForBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="invoice_date", in="query", description="Invoice date. Format m/d/Y", required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function generate(OrderGenerateInvoiceRequest $request, $id): StreamedResponse
    {
        $this->authorize(Permission\Order\BS\OrderGenerateInvoicePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
        );

        return response()->streamDownload(function () use ($model, $request) {
            $this->serviceInvoice->generateInvoicePdf(
                $model,
                $request->invoice_date ? from_bs_timezone('m/d/Y', $request->invoice_date) : null ,
                true
            );
        });
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs/{id}/send-docs",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Send Invoice pdf file for bs order",
     *     operationId="SendInvoicePdfFileForBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderSendDocsRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function send(OrderSendDocsRequest $request, $id): JsonResponse
    {
        $this->authorize(Permission\Order\BS\OrderSendDocumentPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        $this->serviceInvoice->sendDocs(auth_user(), $model, $request->dto());

        return $this->successJsonMessage();
    }
}
