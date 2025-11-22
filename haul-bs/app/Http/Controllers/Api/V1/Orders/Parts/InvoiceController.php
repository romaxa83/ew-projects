<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\BS\OrderGenerateInvoiceRequest;
use App\Http\Requests\Orders\BS\OrderSendDocsRequest;
use App\Models\Orders\Parts\Order;
use App\Repositories\Orders\Parts\OrderRepository;
use App\Services\Orders\Parts\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
     *     path="/api/v1/orders/parts/{id}/generate-invoice",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get Invoice pdf file for parts order",
     *     operationId="GetInvoicePdfFileForPartsOrder",
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
    public function generate(OrderGenerateInvoiceRequest $request, $id): JsonResponse|StreamedResponse
    {
        $this->authorize(Permission\Order\Parts\OrderGenerateInvoicePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        if($model->isDraft()){
            return $this->errorJsonMessage(
                __('exceptions.orders.parts.must_not_be_draft'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

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
     *     path="/api/v1/orders/parts/{id}/send-docs",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Send Invoice pdf file for parts order",
     *     operationId="SendInvoicePdfFileForPartsOrder",
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
        $this->authorize(Permission\Order\Parts\OrderSendDocumentPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        $this->serviceInvoice->sendDocs(auth_user(), $model, $request->dto());

        return $this->successJsonMessage();
    }
}
