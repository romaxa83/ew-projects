<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Exceptions\ErrorsCode;
use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\Order\AAOrderRequest;
use App\Http\Requests\Api\V1\Order\ActRequest;
use App\Http\Requests\Api\V1\Order\BillRequest;
use App\Http\Requests\Api\V1\Order\FreeSlotTimeRequest;
use App\Http\Requests\Api\V1\Order\OrderEditRequest;
use App\Models\AA\AAOrder;
use App\Models\AA\AAOrderPlanning;
use App\Models\AA\AAPost;
use App\Models\AA\AAPostSchedule;
use App\Models\Order\Order;
use App\Repositories\Dealership\DealershipRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Media\File\FileService;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrderController extends ApiController
{
    public function __construct(
        protected OrderRepository $repository,
        protected OrderService $service,
        protected FileService $fileService,
        protected DealershipRepository $dealershipRepository
    )
    {}

    /**
     * @OA\Post (
     *     path="orders/{orderId}",
     *     tags={"Order"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Edit order",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/OrderEdit")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function edit(OrderEditRequest $request, $orderId): JsonResponse
    {
        AALogger::info("Запрос на UPDATE заявки [orderID - {$orderId}]", $request->all());
        try {
            /** @var $order Order */
            $order = $this->repository->getOneBy('uuid', $orderId);

            if(null === $order){
                throw new \Exception("Not found order by [orderId - {$orderId}]",  ErrorsCode::NOT_FOUND);
            }

            $this->service->editFromAA($order, $request->all());

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="orders/{orderId}/invoice",
     *     tags={"Order"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Generate a pdf file for the invoice and attach it to the order",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BillRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function bill(BillRequest $request, $orderId): JsonResponse
    {
        AALogger::info("Запрос на генерирование СЧЕТА заявки [orderID - {$orderId}]", $request->all());
        try {
            /** @var $order Order */
            $order = $this->repository->getOneBy('uuid', $orderId);

            if(null === $order){
                throw new \Exception("Not found order by [orderId - {$orderId}]",  Response::HTTP_BAD_REQUEST);
            }

            $this->fileService->generateOrderPDF($order, $request->all(), Order::FILE_BILL_TYPE);

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="orders/{orderId}/act",
     *     tags={"Order"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Generate a pdf file for the act and attach it to the order",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ActRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */

    public function act(ActRequest $request, $orderId): JsonResponse
    {
        AALogger::info("Запрос на генерирование АКТА заявки [orderID - {$orderId}]", $request->all());
        try {
            /** @var $order Order */
            $order = $this->repository->getOneBy('uuid', $orderId);

            if(null === $order){
                throw new \Exception("Not found order by [orderId - {$orderId}]",  Response::HTTP_BAD_REQUEST);
            }

            $this->fileService->generateOrderPDF($order, $request->all(), Order::FILE_ACT_TYPE);

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="orders/set/free-slot-time",
     *     tags={"Order"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Set free slot time for services",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/FreeSlotTimeRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function freeSlotTime(FreeSlotTimeRequest $request): JsonResponse
    {
        AALogger::info("Запрос на установку свободных слотов времени", $request->all());
        try {
            $countPost = 0;
            $countSchedule = 0;
            $start = microtime(true);
            foreach ($request->all() ?? [] as $item) {

                $post = AAPost::query()->where('uuid', $item['id'])->first();
                if(!$post){
                    $post = new AAPost();
                    $post->uuid = $item['id'];
                    $post->name = $item['name'];
                    $post->alias = $item['alias'];
                    $post->save();
                }

                $countPost++;
                foreach (array_chunk($item['schedule'], 200) ?? [] as $chunk){
                    $temp = [];

                    foreach ($chunk as $key => $data){
                        $temp[$key]['post_id'] = $item['id'];
                        $temp[$key]['date'] = $data['date'];
                        $temp[$key]['start_work'] = $data['startDate'];
                        $temp[$key]['end_work'] = $data['endDate'];
                        $temp[$key]['work_day'] = $data['workingDay'];
                        $countSchedule++;
                    }

                    array_values($temp);

                    \DB::table(AAPostSchedule::TABLE)->upsert(
                        $temp,
                        ['post_id', 'date'],
                        ['start_work', 'end_work', 'work_day']
                    );
                }
            }

            $end = microtime(true) - $start;
            AALogger::info("Загружено/обновлено постов [$countPost]");
            AALogger::info("Загружено/обновлено cлотов [$countSchedule]");
            AALogger::info("Время загрузки [$end]");

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="orders/set/exists",
     *     tags={"Order"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Set exist order",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AAOrderRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function setExists(AAOrderRequest $request)
    {
        AALogger::info("Запрос на запись заявки, для просчета свободного времени", $request->all());
        try {

            $model = new AAOrder();
            $model->order_uuid = $request['id'] ?? null;
            $model->user_uuid = $request['client'] ?? null;
            $model->car_uuid = $request['auto'] ?? null;
            $model->service_alias = $request['type'] ?? null;
            $model->sub_service_alias = $request['subtype'] ?? null;
            $model->dealership_alias = $request['base'] ?? null;
            $model->start_date = $request['startdate'];
            $model->end_date = $request['enddate'];
            $model->post_uuid = $request['workshop'];
            $model->comment = $request['comment'];

            if(!$this->repository->existBy("uuid", $request['id'])){
                $model->is_sys = false;
            }

            $model->save();

            foreach ($request['planning'] ?? [] as $item){
                $p =  new AAOrderPlanning();
                $p->aa_order_id = $model->id;
                $p->post_uuid = $item['workshop'];
                $p->end_date = $item['endDate'];
                $p->start_date = $item['startDate'];
                $p->save();
            }

            return $this->successJsonMessage([]);
        } catch (\Exception $e){

            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
