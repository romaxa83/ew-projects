<?php

namespace App\Http\Swagger\Order;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for edit order",
 *     description="Request for edit order",
 * )
 */
class OrderEdit
{
    /**
     * @OA\Property(
     *     title="Order status",
     *     description="Статусы заявки",
     *     example=2,
     * )
     *
     */
    public int $status;

    /**
     * @OA\Property(
     *     title="Order payment status",
     *     description="Статусы оплаты заявки",
     *     example=2,
     * )
     *
     */
    public int $statusPayment;

    /**
     * @OA\Property(
     *     title="Order responsible person",
     *     description="Ответсвенное лицо по заявке",
     *     example="Иван Иванов",
     * )
     *
     */
    public string $responsible;

    /**
     * @OA\Property(
     *     title="Real date",
     *     description="Дата записи на сервис, фактическое время",
     *     example=163113480,
     * )
     *
     */
    public int $realDate;
}

