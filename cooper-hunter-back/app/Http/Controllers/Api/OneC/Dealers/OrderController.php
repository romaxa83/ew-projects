<?php

namespace App\Http\Controllers\Api\OneC\Dealers;

use App\Dto\Orders\Dealer\Onec\OrderCreateDto;
use App\Dto\Orders\Dealer\OrderFilesDto;
use App\Dto\Orders\Dealer\OrderInvoiceOnecDto;
use App\Dto\Orders\Dealer\OrderOnecDto;
use App\Dto\Orders\Dealer\OrderPackingSlipsOnecDto;
use App\Dto\Orders\Dealer\OrderSerialNumbersOnecDto;
use App\Events\Orders\Dealer\CheckoutOrderEvent;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\OneC\Dealers\OrderAddInvoiceDataRequest;
use App\Http\Requests\Api\OneC\Dealers\OrderAddPackingSlipRequest;
use App\Http\Requests\Api\OneC\Dealers\OrderAddSerialNumberRequest;
use App\Http\Requests\Api\OneC\Dealers\OrderCreateRequest;
use App\Http\Requests\Api\OneC\Dealers\OrderListRequest;
use App\Http\Requests\Api\OneC\Dealers\OrderUpdateRequest;
use App\Http\Requests\Api\OneC\Dealers\OrderUploadFileRequest;
use App\Http\Resources\Api\OneC\Dealer\Order\OrderResource;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use App\Repositories\Orders\Dealer\OrderRepository;
use App\Repositories\Orders\Dealer\PackingSlipRepository;
use App\Services\Orders\Dealer\OrderService;
use App\Services\Orders\Dealer\PackingSlipService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * @group Dealer
 *
 * @enum App\Enums\Orders\Dealer\OrderStatus
 * @enum App\Enums\Orders\Dealer\OrderType
 * @enum App\Enums\Orders\Dealer\DeliveryType
 * @enum App\Enums\Orders\Dealer\PaymentType
 * @enum App\Enums\Orders\OrderArrivedFormEnum
 */
class OrderController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected OrderService $service,
        protected PackingSlipService $packingSlipService,
        protected PackingSlipRepository $packingSlipRepo
    ) {
    }

    /**
     * Dealer order list
     *
     * @responseFile 200 docs/api/orders/dealers/list.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function list(OrderListRequest $request)
    {
        try {
            /** @var $model Collection */
            $models = $this->repo->getAllPagination(
                [
                    'serialNumbers.product',
                    'items.product',
                    'shippingAddress.country',
                    'shippingAddress.state',
                    'dealer.company.country',
                    'dealer.company.state',
                    'packingSlips.dimensions',
                    'packingSlips.items.product',
                    'packingSlips.serialNumbers.product',
                    'packingSlips.dimensions',
                ],
                $request->all()
            );

            return OrderResource::collection($models);
        } catch (Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create dealer order
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function create(OrderCreateRequest $request): JsonResponse
    {
        try {
            makeTransaction(
                fn(): Order => $this->service->createOnec(
                    OrderCreateDto::byArgs($request->all())
                )
            );
            return self::responseSuccess("Done");
        } catch (Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Update dealer order
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function update(OrderUpdateRequest $request, $guid): JsonResponse
    {
        try {
            /** @var $model Order */
            $model = $this->repo->getBy(
                'guid',
                $guid,
                [],
                true,
                __(
                    'exceptions.dealer.order.not found by guid',
                    ['guid' => $guid]
                )
            );

            makeTransaction(
                fn(): Order => $this->service->updateOnec(
                    $model,
                    OrderOnecDto::byArgs($request->all())
                )
            );

            return self::responseSuccess("Done");
        } catch (Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Add packing list to a dealer order
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function addOrUpdatePackingSlip(OrderAddPackingSlipRequest $request, $guid): JsonResponse
    {
        try {
            /** @var $model Order */
            $model = $this->repo->getBy(
                'guid',
                $guid,
                ['items.product'],
                true,
                __(
                    'exceptions.dealer.order.not found by guid',
                    ['guid' => $guid]
                )
            );

            makeTransaction(
                fn() => $this->packingSlipService->addOrUpdatePackingSlips(
                    $model,
                    OrderPackingSlipsOnecDto::byArgs($request['data'])
                )
            );

            return self::responseSuccess("Done");
        } catch (Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Add serial number to packing list
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function addSerialNumberToPackingList(
        OrderAddSerialNumberRequest $request,
        $guid
    ): JsonResponse
    {
        try {
            /** @var $model PackingSlip */
            $model = $this->packingSlipRepo->getBy(
                'guid',
                $guid,
                [],
                true,
                __(
                    'exceptions.dealer.order.packing_slip.not found by guid',
                    ['guid' => $guid]
                )
            );

            $result = makeTransaction(
                fn(): array => $this->packingSlipService->addSerialNumbers(
                    $model,
                    OrderSerialNumbersOnecDto::byArgs($request['data'])
                )
            );

            return self::responseSuccess($result);
        } catch (Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Add a file to dealer order
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function upload(OrderUploadFileRequest $request, $guid): JsonResponse
    {
        try {
            /** @var $model Order */
            $model = $this->repo->getBy(
                'guid',
                $guid,
                [],
                true,
                __(
                    'exceptions.dealer.order.not found by guid',
                    ['guid' => $guid]
                )
            );

            makeTransaction(
                fn() => $this->service->uploadFilesFromOnec(
                    $model,
                    OrderFilesDto::make($request->get('files'))
                )
            );

            return self::responseSuccess("Done");
        } catch (Throwable $e) {
            logger_info($e->getMessage(), [$e]);
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Add a file to packingSlip
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function uploadToPackingSlip(OrderUploadFileRequest $request, $guid): JsonResponse
    {
        try {
            /** @var $model PackingSlip */
            $model = $this->packingSlipRepo->getBy(
                'guid',
                $guid,
                [],
                true,
                __(
                    'exceptions.dealer.order.packing_slip.not found by guid',
                    ['guid' => $guid]
                )
            );

            makeTransaction(
                fn() => $this->packingSlipService->uploadFilesFromOnec(
                    $model,
                    OrderFilesDto::make($request->get('files'))
                )
            );

            return self::responseSuccess("Done");
        } catch (Throwable $e) {
            logger_info($e->getMessage(), [$e]);
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Add invoice data to the order (or the packingSlip)
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function addInvoiceData(OrderAddInvoiceDataRequest $request): JsonResponse
    {
        try {
            if($guid = data_get($request->all() ,'packing_slip_guid')){
                /** @var $model PackingSlip */
                $model = $this->packingSlipRepo->getBy('guid', $guid, [], true,
                    __('exceptions.dealer.order.packing_slip.not found by guid', ['guid' => $guid]));

                $this->packingSlipService->addOrUpdatePackingSlipInvoice(
                    $model,
                    OrderInvoiceOnecDto::byArgs($request->all())
                );
            } else {
                $guid = data_get($request->all() ,'order_guid');
                /** @var $model Order */
                $model = $this->repo->getBy('guid', $guid, [], true,
                    __('exceptions.dealer.order.not found by guid', ['guid' => $guid])
                );

                $this->service->addInvoiceData($model, OrderInvoiceOnecDto::byArgs($request->all()));
            }

            return self::responseSuccess("Done");
        } catch (Throwable $e) {
            logger_info($e->getMessage(), [$e]);
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }
}
