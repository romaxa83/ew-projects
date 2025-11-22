<?php

namespace App\Http\Swagger\User\Car\Order;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Order car edit",
 *     description="data for edit to the order car",
 * )
 */
class Edit
{
    /**
     * @OA\Property(
     *     title="statusPayment",
     *     description="Payment status for the car in the order",
     *     example="1",
     * )
     * @var int
     */
    public int $statusPayment;

    /**
     * @OA\Property(
     *     title="sum",
     *     description="the amount of payment for the car in the order",
     *     example="1000.00",
     * )
     * @var float
     */
    public float $sum;

    /**
     * @OA\Property(
     *     title="sumDiscount",
     *     description="the amount of payment for the car in the order with discount",
     *     example="900.50",
     * )
     * @var float
     */
    public float $sumDiscount;
}
