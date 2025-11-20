<?php

namespace WezomCms\ServicesOrders\Http\Controllers\Api\V1;

use App\Exceptions\RejectOrderStatusException;
use Illuminate\Http\Request;
use Notification;
use WezomCms\Cars\Repositories\ModelRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Core\UseCase\DateFormatter;
use WezomCms\Requests\ConvertData\ScheduleConvert;
use WezomCms\Requests\Helpers\RequestEvents;
use WezomCms\Requests\Helpers\SpareType;
use WezomCms\Requests\Services\Request1CService;
use WezomCms\Services\Repositories\ServiceGroupRepository;
use WezomCms\Services\Types\ServiceType;
use WezomCms\ServicesOrders\DTO\OrderListDto;
use WezomCms\ServicesOrders\Http\Requests\Api\OrderChangeStatusFrom1CRequest;
use WezomCms\ServicesOrders\Http\Requests\Api\OrderFreeTimeRequest;
use WezomCms\ServicesOrders\Http\Requests\Api\OrderRateRequest;
use WezomCms\ServicesOrders\Http\Requests\Api\OrderRequest;
use WezomCms\ServicesOrders\Notifications\ServicesOrderNotification;
use WezomCms\ServicesOrders\Repositories\OrderRepository;
use WezomCms\ServicesOrders\Services\OrderService;
use WezomCms\ServicesOrders\Types\OrderStatus;
use WezomCms\TelegramBot\Telegram;

class OrderController extends ApiController
{
    use CoordsTrait;

    private OrderService $orderService;
    private ServiceGroupRepository $serviceGroupRepository;
    private OrderRepository $orderRepository;
    private ModelRepository $modelRepository;

    public function __construct(
        OrderService $orderService,
        ServiceGroupRepository $serviceGroupRepository,
        OrderRepository $orderRepository,
        ModelRepository $modelRepository
    )
    {
        parent::__construct();
        $this->orderService = $orderService;
        $this->serviceGroupRepository = $serviceGroupRepository;
        $this->orderRepository = $orderRepository;
        $this->modelRepository = $modelRepository;
    }

    public function list(Request $request)
    {
        try {

            $orders = $this->orderRepository->getAll(
                ['car', 'city', 'service', 'group', 'dealership'], 'id', $request->all(), false
            );

            $dtoList = resolve(OrderListDto::class)
                ->setCount($this->orderRepository->count(false))
                ->setCollection($orders)
            ;

            if($this->checkFromRequest($request->all())){
                $dtoList->setPoint($this->getPoint());
            }

            return $this->successJsonMessage($dtoList->toList());

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function listCompletedOrder(Request $request)
    {
        $user = \Auth::user();
        $statuses = [OrderStatus::DONE];
        try {

            $orders = $this->orderRepository->getAllByStatuses(
                $user->id,
                ['car', 'city', 'service', 'group', 'dealership'],
                'closed_at',
                $request->all(),
                $statuses,
                OrderStatus::TYPE_COMPLETED
            );

            $dtoList = resolve(OrderListDto::class)
                ->includeTotalCost()
                ->setCount($this->orderRepository->countByStatuses($user->id, $statuses))
                ->setCollection($orders)
            ;

            if($this->checkFromRequest($request->all())){
                $dtoList->setPoint($this->getPoint());
            }

            return $this->successJsonMessage($dtoList->toList());

        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listPlannedOrder(Request $request)
    {
        $user = \Auth::user();
        $statuses = [
            OrderStatus::CREATED,
            OrderStatus::RECEIVED,
            OrderStatus::IN_WORK,
            OrderStatus::ACCEPTED
        ];
        try {
            $orders = $this->orderRepository->getAllByStatuses(
                $user->id,
                ['car', 'city', 'service', 'group', 'dealership'],
                'on_date',
                $request->all(),
                $statuses,
                OrderStatus::TYPE_PLANED
            );

            $dtoList = resolve(OrderListDto::class)
                ->setCount($this->orderRepository->countByStatuses($user->id, $statuses))
                ->setCollection($orders)
            ;

            if($this->checkFromRequest($request->all())){
                $dtoList->setPoint($this->getPoint());
            }

            return $this->successJsonMessage($dtoList->toList());
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function create(OrderRequest $request)
    {
        Telegram::event("ORDER CREATE DATA : ");
        Telegram::event(serialize($request->all()));
        $user = \Auth::user();
        try {
            $service = $this->serviceGroupRepository->getServiceGroupByType(ServiceType::getTypeBy($request['type']));

            $order = $this->orderService->create($request->all(), $user, $service->id);

            RequestEvents::sendOrder($order);

            $administrators = Administrator::toNotifications('services-orders.index')->get();
            Notification::send($administrators, new ServicesOrderNotification($order));

            return $this->successEmptyMessage();
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function addRate(OrderRateRequest $request, $id)
    {
        try {
            $order = $this->orderRepository->byId($id, [], 'id', false);
            $this->orderService->addRate($order, $request->all());

            return $this->successEmptyMessage();
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function time(OrderFreeTimeRequest $request)
    {
        Telegram::event('DATA TO ORDER TIME - ' . serialize($request->all()));
        $user = \Auth::user();
        try {
            if(!ServiceType::getFreeTime($request['type'])){
                throw new \Exception(__('cms-services::site.exception.not request for free time', ['type' => $request['type']]));
            }

            // если есть модельId, запрашивем модель чтоб отправить для 1с их id (niko_id)
            $modelId = null;
            if(isset($request['modelId']) && !empty($request['modelId'])){
                if($m = $this->modelRepository->byId($request['modelId'], [], 'id', false)){
                    $modelId = $m->niko_id;
                }
            }

            $time = DateFormatter::convertFor1c($request['timestamp']);

            $serviceType = SpareType::check($request['type'], $request['serviceId'] ?? null);

            Telegram::event('DATE TO ORDER - ' . $time);

            $req = \App::make(Request1CService::class);
            $response = $req->orderTime(
                ScheduleConvert::toRequest(
                    $serviceType,
                    $user->id,
                    $request['dealerId'],
                    $time,
                    $modelId
                )
            );
            return $this->successJsonMessage(ScheduleConvert::fromResponse($response, $time, $request['dealerId'], $request['type']));
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    /**
     * @param OrderChangeStatusFrom1CRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(OrderChangeStatusFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел запрос на смену сатуса для заявки');
            Telegram::event(serialize($request->all()));

            $order = $this->orderRepository->byId($request['ApplicationID'], [], 'id', false);

            if(!$order){
                return $this->successJsonCustomMessage(['success' => false, 'message' => 'not found order'], 404);
            }

            $order = $this->orderService->setStatusFrom1C($order, $request);


            return $this->successJsonCustomMessage(['success' => true, 'message' => 'status changed'], 200);

        }
        catch(RejectOrderStatusException $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 423);
        }
        catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }

    public function changeStatusTestMode(OrderChangeStatusFrom1CRequest $request)
    {
        try {
            Telegram::event('От 1с пришел ТЕСТОВЫЙ запрос на смену сатуса для заявки');

            return $this->successJsonCustomMessage(['success' => true, 'message' => 'status changed'], 200);

        } catch(\Exception $exception){
            return $this->successJsonCustomMessage(['success' => false, 'message' => $exception->getMessage()], 500);
        }
    }
}
