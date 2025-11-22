<?php

namespace App\Http\Swagger\User\Car;

use App\Http\Swagger\User\Car\Order\Add;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for add car to user",
 *     description="Request for add car to user",
 * )
 */
class UserCarAdd
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="car id",
     *     example="9ee4670f-0016-11ec-8274-4cd98fc26f15",
     * )
     * @var string
     */
    public string $id;

    /**
     * @OA\Property(
     *     title="brand",
     *     description="brand id",
     *     example="77f2388e-f4ee-11eb-8274-4cd98fc26f15",
     * )
     * @var string
     */
    public string $brand;

    /**
     * @OA\Property(
     *     title="model",
     *     description="model id",
     *     example="74b0db6b-f4f3-11eb-8274-4cd98fc26f15",
     * )
     * @var string
     */
    public string $model;

    /**
     * @OA\Property(
     *     title="year",
     *     description="year",
     *     example="2010",
     * )
     * @var string
     */
    public string $year;

    /**
     * @OA\Property(
     *     title="yearDeal",
     *     description="yearDeal",
     *     example="2011",
     * )
     * @var string
     */
    public string $yearDeal;

    /**
     * @OA\Property(
     *     title="vin",
     *     description="vin",
     *     example="QW1243DF",
     * )
     * @var string
     */
    public string $vin;

    /**
     * @OA\Property(
     *     title="number",
     *     description="number",
     *     example="QW1243DF",
     * )
     * @var string
     */
    public string $number;

    /**
     * @OA\Property(
     *     title="owner",
     *     description="owner id",
     *     example="9ee4670f-0016-11ec-8274-4cd98fc26f15",
     * )
     * @var string
     */
    public string $owner;

    /**
     * @OA\Property(
     *     title="personal",
     *     description="personal",
     *     example=true,
     * )
     * @var boolean
     */
    public bool $personal;

    /**
     * @OA\Property(
     *     title="buy",
     *     description="buy",
     *     example=true,
     * )
     * @var boolean
     */
    public bool $buy;

    /**
     * @OA\Property(
     *     title="verify",
     *     description="Verify car",
     *     example=true,
     * )
     * @var boolean
     */
    public bool $verify;

    /**
     * @OA\Property(
     *     title="status car",
     *     description="Status car",
     *     example=true,
     * )
     * @var boolean
     */
    public bool $statusCar;

    /**
     * @OA\Property(
     *     title="name",
     *     description="Car name",
     *     example="Octavia",
     * )
     * @var string
     */
    public string $name;

    /**
     * @OA\Property(
     *     title="Order car",
     *     @OA\Schema(ref="#/components/schemas/Add")
     * )
     *
     * @var Add
     */
    public $orderCar;

    /**
     * @OA\Property(
     *     title="Proxies",
     *     @OA\Schema(ref="#/components/schemas/Confidants")
     * )
     *
     * @var Confidants[]
     */
    public $proxies;
}
