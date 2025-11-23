<?php

namespace WezomCms\Orders\Contracts;

use Illuminate\Contracts\View\View;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;

interface DeliveryDriverInterface
{
    /**
     * @return array
     */
    public function getFormInputs(): array;

    /**
     * @param  array  $deliveryData
     * @return mixed
     */
    public function renderFormInputs(array $deliveryData);

    /**
     * Get validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array;

    /**
     * Fill database storage.
     *
     * @param  OrderDeliveryInformation  $storage
     * @param  array  $data
     */
    public function fillStorage(OrderDeliveryInformation $storage, array $data);

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormInputs(OrderDeliveryInformation $storage): View;

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return View
     */
    public function renderAdminFormData(OrderDeliveryInformation $storage): View;

    /**
     * @param  OrderDeliveryInformation  $storage
     * @return string
     */
    public function presentDeliveryAddress(OrderDeliveryInformation $storage): string;

    /**
     * @param Order $order
     */
    public function handleOrderUpdate(Order $order): void;
}
