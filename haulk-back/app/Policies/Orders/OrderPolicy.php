<?php

namespace App\Policies\Orders;

use App\Models\Orders\Order;
use App\Models\Users\User;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;


    /**
     * @param User $user
     * @throws Exception
     */

    /**
     * Determine whether the user can create orders.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->can('orders create')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the orders list.
     *
     * @param User $user
     * @return mixed
     */
    public function viewList(User $user)
    {
        /*if ($user->can('orders read own')) {
            return true;
        } else*/ if ($user->can('orders read')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param  User  $user
     * @param  Order  $order
     * @return mixed
     */
    public function view(User $user, Order $order)
    {
        /*if ($user->can('orders read own') && $user->id == $order->user_id) {
            return true;
        } else*/ if ($user->can('orders read')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param  User  $user
     * @param  Order  $order
     * @return mixed
     */
    public function viewAssignedToMe(User $user, Order $order)
    {
        if ($user->can('orders read') && $user->id == $order->driver_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param  User  $user
     * @param  Order  $order
     * @return mixed
     */
    public function sendInvoiceMobile(User $user, Order $order)
    {
        if ($user->can('orders send-invoice') && ($user->carrier->notificationSettings && $user->carrier->notificationSettings->is_invoice_allowed)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the order.
     *
     * @param  User  $user
     * @param  Order  $order
     * @return mixed
     */
    public function update(User $user, Order $order)
    {
        if ($user->can('orders update')) {
            return true;
        } elseif ($user->can('orders update-own') && $user->id == $order->dispatcher_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the order.
     *
     * @param  User  $user
     * @param  Order  $order
     * @return mixed
     */
    public function delete(User $user, Order $order)
    {
        if ($user->can('orders delete')) {
            return true;
        } elseif ($user->can('orders delete-own') && $user->id == $order->dispatcher_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the order.
     *
     * @param  User  $user
     * @param  Order  $order
     * @return mixed
     */
    public function restore(User $user, Order $order)
    {
        if ($user->can('orders restore-own') && $user->id == $order->dispatcher_id) {
            return true;
        } elseif ($user->can('orders restore')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the orders list.
     *
     * @param User $user
     * @return mixed
     */
    public function orderReview(User $user)
    {
        if (
            $user->can('orders order-review')
            || (
                $user->can('orders')
                && $user->can_check_orders
            )
        ) {
            return true;
        }

        return false;
    }

    public function changeStatus(User $user, Order $order): bool
    {
        if ($user->can('orders change-status-own') && $user->id == $order->dispatcher_id) {
            return true;
        } elseif ($user->can('orders change-status')) {
            return true;
        }

        return false;
    }

    public function allowEmptyInspection(User $user, Order $order): bool
    {
        if (!$user->can('orders inspection') || !$user->can('viewAssignedToMe', $order)) {
            return false;
        }

        if (!in_array($order->inspection_type, [Order::INSPECTION_TYPE_NONE, Order::INSPECTION_TYPE_NONE_W_FILE])) {
            return false;
        }

        return true;
    }
}
