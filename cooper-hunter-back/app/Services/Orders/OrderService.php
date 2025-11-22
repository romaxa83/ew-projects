<?php

namespace App\Services\Orders;

use App\Contracts\Roles\HasGuardUser;
use App\Dto\Orders\OrderDto;
use App\Dto\Orders\OrderPartDto;
use App\Dto\Orders\OrderPaymentDto;
use App\Dto\Orders\OrderShippingDto;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Orders\OrderCantPaidException;
use App\Exceptions\Orders\OrderNotFoundException;
use App\Exceptions\Orders\OrderPartPriceIsRequiredException;
use App\Exceptions\Orders\OrderShippingTrkNumberWasAssignedException;
use App\Exceptions\Orders\SerialNumberDoesNotConnectToProjectException;
use App\Models\Admins\Admin;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPart;
use App\Models\Orders\OrderPayment;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use App\Services\Catalog\ProductService;
use App\Services\Payment\PayPalService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderStatusService $orderStatusService,
        private ProductService $productService,
        private PayPalService $payPalService
    ) {
    }

    public function update(string $orderId, OrderDto $dto, Technician $technician, Admin $admin): Order
    {
        $order = $this->getOrder($orderId, $admin);

        return $this->modifyOrder($order, $dto, $technician);
    }

    /**
     * @param string $orderId
     * @param HasGuardUser $user
     * @return Order
     */
    private function getOrder(string $orderId, HasGuardUser $user): Order
    {
        $order = Order::forGuard($user)
            ->find($orderId);

        if (!$order) {
            throw new OrderNotFoundException();
        }

        return $order;
    }

    private function modifyOrder(Order $order, OrderDto $dto, Technician $technician): Order
    {
        $this->checkConnectSerialNumberToProject($technician, $dto->getSerialNumber(), $dto->getProjectId());

        $order->technician_id = $technician->id;
        $order->project_id = $dto->getProjectId();
        $order->product_id = $this->productService->searchProductBySerialNumber($dto->getSerialNumber())->id;
        $order->serial_number = $dto->getSerialNumber();
        $order->first_name = $dto->getFirstName();
        $order->last_name = $dto->getLastName();
        $order->phone = $dto->getPhone();
        $order->comment = $dto->getComment();

        if ($dto->getOrderStatus()) {
            $order->status = $dto->getOrderStatus();
        }

        $order->save();

        $this->saveParts(
            $order,
            $dto->getParts(),
            $dto->getPayment()
                ->isExistsPayment()
        );

        $this->saveShippingData($order, $dto->getShipping());

        $this->savePaymentData($order, $dto->getPayment());

        $order->refresh();

        $this->orderStatusService->autoChangeStatus($order);

        $order->refresh();

        return $order;
    }

    private function checkConnectSerialNumberToProject(
        Technician $technician,
        string $serialNumber,
        ?int $projectId
    ): void {
        if (empty($projectId)) {
            return;
        }

        /**@var SystemUnitPivot $systemUnit */
        $systemUnit = SystemUnitPivot::whereSerialNumber($serialNumber)
            ->whereHas(
                'system',
                fn(Builder $builder) => $builder->whereHas(
                    'project',
                    fn(Builder|Project $projectBuilder) => $projectBuilder
                        ->whereMember($technician)
                )
            )
            ->first();

        if (!$systemUnit || $systemUnit->system->project_id !== $projectId) {
            throw new SerialNumberDoesNotConnectToProjectException();
        }
    }

    /**
     * @param Order $order
     * @param OrderPartDto[] $parts
     * @param bool $isExistsPayment
     */
    private function saveParts(Order $order, array $parts, bool $isExistsPayment): void
    {
        $order->parts()
            ->delete();

        foreach ($parts as $part) {
            if ($isExistsPayment && $part->getPrice() === null) {
                throw new OrderPartPriceIsRequiredException();
            }
            $orderPart = new OrderPart();
            $orderPart->order_id = $order->id;
            $orderPart->order_category_id = $part->getId();
            $orderPart->quantity = $part->getQuantity();
            $orderPart->description = $part->getDescription();
            $orderPart->price = $part->getPrice();
            $orderPart->save();
        }
    }

    public function delete(string $orderId, Admin $admin): bool
    {
        $this->getOrder($orderId, $admin)
            ->forceDelete();

        return true;
    }

    private function saveShippingData(Order $order, OrderShippingDto $dto): void
    {
        $order->shipping()
            ->updateOrCreate(
                [
                    'order_id' => $order->id
                ],
                [
                    'first_name' => $dto->getFirstName(),
                    'last_name' => $dto->getLastName(),
                    'phone' => $dto->getPhone(),
                    'address_first_line' => $dto->getAddressFirstLine(),
                    'address_second_line' => $dto->getAddressSecondLine(),
                    'city' => $dto->getCity(),
                    'country_id' => $dto->getCountryID(),
                    'state_id' => $dto->getStateID(),
                    'zip' => $dto->getZip(),
                    'order_delivery_type_id' => $dto->getDeliveryType(),
                    'trk_number' => $dto->getTrkNumber()
                ]
            );
    }

    private function savePaymentData(Order $order, OrderPaymentDto $dto): void
    {
        $order->payment()
            ->updateOrCreate(
                [
                    OrderPayment::TABLE . '.order_id' => $order->id,
                ],
                [
                    'order_price' => $dto->getOrderPrice(),
                    'order_price_with_discount' => $dto->getOrderPriceWithDiscount(),
                    'shipping_cost' => $dto->getShippingCost(),
                    'tax' => $dto->getTax(),
                    'discount' => $dto->getDiscount(),
                    'paid_at' => $dto->getPaidAt()
                ]
            );
    }

    public function updateByApi(Order $order, OrderDto $dto): Order
    {
        return $this->modifyOrder($order, $dto, $order->technician);
    }

    public function updateShippingData(string $orderId, HasGuardUser $user, OrderShippingDto $dto): Order
    {
        $order = $this->getOrder($orderId, $user);

        if ($order->shipping->trk_number && !$user instanceof Admin) {
            throw new OrderShippingTrkNumberWasAssignedException();
        }

        $this->saveShippingData($order, $dto);

        $order->refresh();

        return $order;
    }

    public function updatePaymentData(string $orderId, HasGuardUser $user, OrderPaymentDto $dto): Order
    {
        $order = $this->getOrder($orderId, $user);

        $this->savePaymentData($order, $dto);

        $this->orderStatusService->autoChangeStatus($order);

        $order->refresh();

        return $order;
    }

    /**
     * @param string $orderId
     * @param HasGuardUser $user
     * @param OrderPartDto[] $dto
     * @return Order
     */
    public function updateParts(string $orderId, array $dto, HasGuardUser $user): Order
    {
        $order = $this->getOrder($orderId, $user);

        $order->parts()
            ->delete();

        $this->saveParts($order, $dto, $order->payment->order_price !== null);

        return $order->refresh();
    }

    public function getList(array $args, HasGuardUser $user): LengthAwarePaginator
    {
        return Order::filter($args)
            ->forGuard($user)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(perPage: $args['limit'], page: $args['page']);
    }

    /**
     * @param string $orderId
     * @param HasGuardUser $user
     * @return Order
     */
    public function connectProject(string $orderId, HasGuardUser $user): Order
    {
        $project = $this->getAvailableProject($orderId, $user);

        if ($project === null) {
            throw new SerialNumberDoesNotConnectToProjectException();
        }

        $order = Order::find($orderId);

        $order->project_id = $project->id;
        $order->save();

        return $order;
    }

    /**
     * @param Order|string $order
     * @param HasGuardUser $user
     * @return Project|null
     */
    public function getAvailableProject(Order|string $order, HasGuardUser $user): ?Project
    {
        $order = is_string($order) ? $this->getOrder($order, $user) : $order;

        if ($order->project_id) {
            return null;
        }

        $systemUnit = SystemUnitPivot::whereSerialNumber($order->serial_number)
            ->whereHas(
                'system',
                fn(Builder $builder) => $builder->whereHas(
                    'project',
                    fn(Builder|Project $projectBuilder) => $projectBuilder
                        ->whereMember($order->technician)
                        ->orWhereHas(
                            'technicians',
                            fn(Builder $technicianBuilder) => $technicianBuilder->where(
                                'technician_id',
                                $order->technician->id
                            )
                        )
                )
            )
            ->first();

        if (!$systemUnit) {
            return null;
        }

        return $systemUnit->system->project;
    }

    /**
     * @param string $orderId
     * @param HasGuardUser $user
     * @return Order
     */
    public function disconnectProject(string $orderId, HasGuardUser $user): Order
    {
        $order = $this->getOrder($orderId, $user);

        if (!$order->project_id) {
            return $order;
        }

        $order->project_id = null;
        $order->save();

        return $order;
    }

    public function getOrderProjects(array $args, HasGuardUser $user): ?Collection
    {
        $query = Order::forGuard($user)
            ->filter($args)
            ->selectRaw('project_id, COUNT(*) as orders')
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->orderByDesc('orders');

        return DB::table(
            DB::raw("(" . $query->toSql() . ") AS order_projects")
        )
            ->setBindings($query->getBindings())
            ->leftJoin(Project::TABLE, 'project_id', '=', 'id')
            ->get();
    }

    /**
     * @param string $orderId
     * @param Technician $technician
     * @return Order
     */
    public function setCanceledStatus(string $orderId, Technician $technician): Order
    {
        $order = $this->getOrder($orderId, $technician);

        if ($order->status->value === OrderStatusEnum::PAID) {
            $this->payPalService->refundPayment($order);
        }

        $order->status = OrderStatusEnum::CANCELED;
        $order->save();

        return $order;
    }

    public function getTotalData(HasGuardUser $user): Collection
    {
        $total = Order::forGuard($user)
            ->selectRaw(
                "
            SUM(CASE
                WHEN status IN (?, ?, ?) THEN 1
                ELSE 0
            END) AS active,
            SUM(CASE
                WHEN status IN (?, ?) THEN 1
                ELSE 0
            END) AS history,
            COUNT(*) AS total
        ",
                [
                    OrderStatusEnum::CREATED,
                    OrderStatusEnum::PENDING_PAID,
                    OrderStatusEnum::PAID,
                    OrderStatusEnum::SHIPPED,
                    OrderStatusEnum::CANCELED
                ]
            )
            ->first();

        return collect(
            [
                'active' => $total->active ?? 0,
                'history' => $total->history ?? 0,
                'total' => $total->total ?? 0,
            ]
        );
    }

    public function getCounterData(HasGuardUser $user): Collection
    {
        $total = Order::forGuard($user)
            ->selectRaw(
                "
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS created,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS pending_paid,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS paid,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS shipped,
            SUM(CASE
                WHEN status = ? THEN 1
                ELSE 0
            END) AS canceled,
            COUNT(*) AS total
        ",
                [
                    OrderStatusEnum::CREATED,
                    OrderStatusEnum::PENDING_PAID,
                    OrderStatusEnum::PAID,
                    OrderStatusEnum::SHIPPED,
                    OrderStatusEnum::CANCELED
                ]
            )
            ->first();

        return collect(
            [
                'created' => $total->created ?? 0,
                'pending_paid' => $total->pending_paid ?? 0,
                'paid' => $total->paid ?? 0,
                'shipped' => $total->shipped ?? 0,
                'canceled' => $total->canceled ?? 0,
                'total' => $total->total ?? 0,
            ]
        );
    }

    public function changeStatus(string $orderId, string $status, Admin $admin): Order
    {
        $order = $this->getOrder($orderId, $admin);

        if ($order->status === $status) {
            return $order;
        }

        $order->status = $status;
        $order->save();

        $this->orderStatusService->autoChangeStatus($order);

        $order->refresh();

        return $order;
    }

    public function updateComment(string $orderId, ?string $comment, Admin $admin): Order
    {
        $order = $this->getOrder($orderId, $admin);
        $order->comment = $comment;
        $order->save();

        return $order;
    }

    public function changeTechnician(string $orderId, Technician $technician, Admin $admin): Order
    {
        $order = $this->getOrder($orderId, $admin);

        if ($order->technician_id === $technician->id) {
            return $order;
        }
        $projectExists = $order->project_id !== null;

        $order->technician_id = $technician->id;
        $order->project_id = null;

        $order->save();

        if ($projectExists === false) {
            return $order->refresh();
        }

        return $this->autoChangeProject($order, $technician);
    }

    private function autoChangeProject(Order $order, Technician $technician): Order
    {
        $project = $this->getAvailableProject($order, $technician);

        if ($project === null) {
            return $order;
        }

        $order->project_id = $project->id;
        $order->save();

        return $order->refresh();
    }

    public function changeSerialNumber(string $orderId, string $serialNumber, Admin $admin): Order
    {
        $order = $this->getOrder($orderId, $admin);

        if ($order->serial_number === $serialNumber) {
            return $order;
        }

        $projectExists = $order->project_id !== null;

        $order->serial_number = $serialNumber;
        $order->product_id = $this->productService->searchProductBySerialNumber($serialNumber)->id;
        $order->project_id = null;

        $order->save();

        if ($projectExists === false) {
            return $order->refresh();
        }

        return $this->autoChangeProject($order, $order->technician);
    }

    public function payForOrder(string $orderId, string $platform, Technician $technician): Collection
    {
        $order = $this->getOrder($orderId, $technician);

        if ($order->status->value !== OrderStatusEnum::PENDING_PAID) {
            throw new OrderCantPaidException();
        }

        return collect(
            [
                'url' => $this->payPalService
                    ->getApproveUrl($order, $platform)
            ]
        );
    }

    public function setPaid(Order $order): void
    {
        if ($order->status->value === OrderStatusEnum::PAID) {
            return;
        }

        $order->status = OrderStatusEnum::PAID;
        $order->save();

        $order->payment->paid_at = Carbon::now()
            ->getTimestamp();
        $order->payment->save();
    }

    public function setRefunded(Order $order): void
    {
        $order->status = OrderStatusEnum::CANCELED;
        $order->save();

        $order->payment->refund_at = Carbon::now()
            ->getTimestamp();
        $order->payment->save();
    }

    public function checkPaid(string $orderId, ?string $tokenId, Technician $technician): OrderPayment
    {
        $order = $this->getOrder($orderId, $technician);

        if ($order->status->value !== OrderStatusEnum::PENDING_PAID || !$tokenId) {
            return $order->payment;
        }

        $this->payPalService->isApprovedCheckout($order, $tokenId);

        return $order->refresh()->payment;
    }

    public function createByTicket(Ticket $ticket, OrderDto $dto, Technician $user): Order
    {
        $order = $this->create($dto, $user);

        $order->ticket()->associate($ticket);
        $order->save();

        return $order;
    }

    public function create(OrderDto $dto, Technician $technician): Order
    {
        return $this->modifyOrder(new Order(), $dto, $technician);
    }
}
