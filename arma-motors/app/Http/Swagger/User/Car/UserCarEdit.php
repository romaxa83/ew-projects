<?php

namespace App\Http\Swagger\User\Car;

use App\Http\Swagger\User\Car\Order\Edit;
use App\Models\User\Confidant;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for edit user's car",
 *     description="Request for edit user's car",
 * )
 */
class UserCarEdit
{
    /**
     * @OA\Property(
     *     title="number",
     *     description="Car number",
     *     example="AA0001AA",
     * )
     * @var string
     */
    public string $number;

    /**
     * @OA\Property(
     *     title="vin",
     *     description="Car vin",
     *     example="1KLBN52TWXM186109",
     * )
     * @var string
     */
    public string $vin;

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
     *     title="year",
     *     description="year",
     *     example="2010",
     * )
     * @var string
     */
    public string $year;

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
     *     title="verify",
     *     description="Verify car",
     *     example=true,
     * )
     * @var boolean
     */
    public bool $verify;

    /**
     * @OA\Property(
     *     title="Order car",
     *     @OA\Schema(ref="#/components/schemas/Edit")
     * )
     *
     * @var Edit
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
