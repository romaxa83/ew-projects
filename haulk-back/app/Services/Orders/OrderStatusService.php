<?php

namespace App\Services\Orders;

use App\Exceptions\Order\ChangeOrderStatusException;
use App\Models\Orders\Order;
use App\Models\Users\User;
use App\Services\Events\EventService;
use DB;
use Exception;
use Log;
use Throwable;

class OrderStatusService
{
    private User $user;

    public function setUser(User $user): OrderStatusService
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Order $order
     * @return Order
     * @throws Throwable
     */
    public function autoChangeStatus(Order $order): Order
    {
        if ($order->canGrantDeliveredStatus()) {
            return $this->grantDeliveredStatus($order);
        }

        if ($order->canGrantPickedUpStatus()) {
            return $this->grantPickedUpStatus($order);
        }

        return $order;
    }

    /**
     * @param Order $order
     * @return Order
     * @throws Throwable
     */
    private function grantDeliveredStatus(Order $order): Order
    {
        try {
            $order->status = Order::STATUS_DELIVERED;
            $order->is_manual_change_to_delivery = false;

            OrderPaymentService::init()->updatePlannedDate($order->payment);

            $order->saveOrFail();

            EventService::status($order)
                ->user($this->user)
                ->auto()
                ->change()
                ->broadcast();

            return $order;
        } catch (Throwable $e) {

            Log::error($e);

            throw $e;
        }
    }

    private function grantPickedUpStatus(Order $order): Order
    {
        try {
            $order->status = Order::STATUS_PICKED_UP;
            $order->is_manual_change_to_pickup = false;

            OrderPaymentService::init()->updatePlannedDate($order->payment);

            $order->saveOrFail();

            EventService::status($order)
                ->user($this->user)
                ->auto()
                ->change()
                ->push()
                ->broadcast();

            return $order;
        } catch (Throwable $e) {
            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param string $status
     * @param array $dates
     * @return Order
     * @throws ChangeOrderStatusException|Throwable
     */
    public function changeStatus(Order $order, string $status, array $dates): Order
    {
        switch ($status) {
            case Order::CALCULATED_STATUS_NEW:
                return $this->toNew($order);
            case Order::CALCULATED_STATUS_ASSIGNED:
                return $this->toAssigned($order);
            case Order::CALCULATED_STATUS_PICKED_UP:
                return $this->toPickedUp($order, $dates);
            case Order::CALCULATED_STATUS_DELIVERED:
                return $this->toDelivered($order, $dates);
        }
        return $order;
    }

    /**
     * @param Order $order
     * @return Order
     * @throws ChangeOrderStatusException
     */
    private function toNew(Order $order): Order
    {
        if ($order->isStatusAssigned()) {
            $order->driver_id = null;
            $order->save();

            EventService::status($order)
                ->user($this->user)
                ->old(Order::CALCULATED_STATUS_ASSIGNED)
                ->change(Order::CALCULATED_STATUS_NEW)
                ->broadcast();

            return $order;
        }

        throw new ChangeOrderStatusException();
    }

    /**
     * @param Order $order
     * @return Order
     * @throws ChangeOrderStatusException
     * @throws Throwable
     */
    private function toAssigned(Order $order): Order
    {
        if ($order->isStatusPickedUp()) {
            $order = $this->stepBack($order);

            EventService::status($order)
                ->user($this->user)
                ->old(Order::CALCULATED_STATUS_PICKED_UP)
                ->change(Order::CALCULATED_STATUS_ASSIGNED)
                ->broadcast();

            return $order;
        }

        throw new ChangeOrderStatusException();
    }

    /**
     * @param Order $order
     * @param array $dates
     * @return Order
     * @throws ChangeOrderStatusException
     * @throws Throwable
     */
    private function toPickedUp(Order $order, array $dates): Order
    {
//        $order->update([
//            'driver_pickup_id' => $order->driver_id
//        ]);

        if ($order->isStatusAssigned()) {
            $order->update(['is_manual_change_to_pickup' => true]);
            $order = $this->makePickedUp($order, $dates['pickup_date_actual']);

            EventService::status($order)
                ->user($this->user)
                ->old(Order::CALCULATED_STATUS_ASSIGNED)
                ->change(Order::CALCULATED_STATUS_PICKED_UP)
                ->broadcast();

            return $order;
        } elseif ($order->isStatusDelivered()) {
            $order = $this->stepBack($order);

            EventService::status($order)
                ->user($this->user)
                ->old(Order::CALCULATED_STATUS_DELIVERED)
                ->change(Order::CALCULATED_STATUS_PICKED_UP)
                ->push()
                ->broadcast();

            return $order;
        }

        throw new ChangeOrderStatusException();
    }

    /**
     * @param Order $order
     * @param array $dates
     * @return Order
     * @throws ChangeOrderStatusException
     */
    private function toDelivered(Order $order, array $dates): Order
    {
        if ($order->isStatusAssigned()) {
            $order->update([
                'is_manual_change_to_delivery' => true,
                'is_manual_change_to_pickup' => true
            ]);
            $order = $this->makeDelivered($order, $dates['pickup_date_actual'], $dates['delivery_date_actual']);

            EventService::status($order)
                ->user($this->user)
                ->old(Order::CALCULATED_STATUS_ASSIGNED)
                ->change(Order::CALCULATED_STATUS_DELIVERED)
                ->broadcast();

            return $order;
        } elseif ($order->isStatusPickedUp()) {
            $order->update(['is_manual_change_to_delivery' => true]);
            $order = $this->makeDelivered($order, null, $dates['delivery_date_actual']);

            EventService::status($order)
                ->user($this->user)
                ->old(Order::CALCULATED_STATUS_PICKED_UP)
                ->change(Order::CALCULATED_STATUS_DELIVERED)
                ->push();

            return $order;
        }

        throw new ChangeOrderStatusException();
    }

    private function makePickedUp(Order $order, $pickup_date_actual = null): Order
    {
        try {
            DB::beginTransaction();

            foreach ($order->vehicles as $vehicle) {
                $vehicle->givePickupInspection();
            }

            $order->setPickupCompletedFields($pickup_date_actual);
            $order->save();

            OrderPaymentService::init()->updatePlannedDate($order->payment);

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function makeDelivered(Order $order, $pickup_date_actual = null, $delivery_date_actual = null): Order
    {
        try {
            DB::beginTransaction();

            foreach ($order->vehicles as $vehicle) {
                $vehicle->giveDeliveryInspection($order->isStatusAssigned());
            }

            if ($order->isStatusAssigned()) {
                $order->setPickupCompletedFields($pickup_date_actual);
            }

            $order->setDeliveryCompletedFields($delivery_date_actual);
            $order->save();

            OrderPaymentService::init()->updatePlannedDate($order->payment);

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @return Order
     * @throws Throwable
     */
    private function stepBack(Order $order): Order
    {
        try {
            DB::beginTransaction();

            if ($order->status === Order::STATUS_DELIVERED) {
                foreach ($order->vehicles as $vehicle) {
                    if (!$vehicle->deliveryInspection) {
                        continue;
                    }

                    $deliveryInspection = $vehicle->deliveryInspection;

                    if ($deliveryInspection->media->count()) {
                        $deliveryInspection->media->each->delete();
                    }

                    $deliveryInspection->fill(
                        [
                            'condition_dark' => false,
                            'condition_snow' => false,
                            'condition_rain' => false,
                            'condition_dirty_vehicle' => false,

                            'odometer' => null,
                            'notes' => null,

                            'num_keys' => 0,
                            'num_remotes' => 0,
                            'num_headrests' => 0,

                            'drivable' => false,
                            'windscreen' => false,
                            'glass_all_intact' => false,
                            'title' => false,
                            'cargo_cover' => false,
                            'spare_tire' => false,
                            'radio' => false,
                            'manuals' => false,
                            'navigation_disk' => false,
                            'plugin_charger_cable' => false,
                            'headphones' => false,
                        ]
                    );

                    $deliveryInspection->vin = $vehicle->pickupInspection->vin;
                    $deliveryInspection->has_vin_inspection = $vehicle->pickupInspection->has_vin_inspection;

                    $deliveryInspection->save();
                }

                $order->status = Order::STATUS_PICKED_UP;
                $order->has_delivery_inspection = false;
                $order->has_delivery_signature = false;
                $order->delivery_date_actual = null;
                $order->delivery_customer_not_available = false;
                $order->delivery_customer_full_name = null;

                // if customer payment
                if ($order->payment && $order->payment->customer_payment_amount) {
                    $order->payment->resetDriverPaymentData();
                }

                $order->clearMediaCollection(Order::DELIVERY_CUSTOMER_SIGNATURE_COLLECTION_NAME);
                $order->clearMediaCollection(Order::DELIVERY_DRIVER_SIGNATURE_COLLECTION_NAME);

                $order->save();
            } elseif ($order->status === Order::STATUS_PICKED_UP) {
                if ($order->vehicles) {
                    foreach ($order->vehicles as $vehicle) {
                        if ($vehicle->deliveryInspection) {
                            $vehicle->deliveryInspection->delete();
                            $vehicle->delivery_inspection_id = null;
                        }

                        if ($vehicle->pickupInspection) {
                            $vehicle->pickupInspection->delete();
                            $vehicle->pickup_inspection_id = null;
                        }

                        $vehicle->restoreOldValues();
                        $vehicle->save();
                    }
                }

                $order->status = Order::STATUS_NEW;
                $order->has_pickup_inspection = false;
                $order->has_pickup_signature = false;
                $order->pickup_date_actual = null;
                $order->pickup_customer_not_available = false;
                $order->pickup_customer_full_name = null;

                if ($order->payment) {
                    $order->payment->resetDriverPaymentData();
                }

                $order->clearMediaCollection(Order::PICKUP_CUSTOMER_SIGNATURE_COLLECTION_NAME);
                $order->clearMediaCollection(Order::PICKUP_DRIVER_SIGNATURE_COLLECTION_NAME);

                $order->save();
            }

            OrderPaymentService::init()->updatePlannedDate($order->payment);

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }
}
