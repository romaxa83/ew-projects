<?php

namespace App\Http\Swagger\Catalog;

/**
 * @OA\Schema(
 *     title="Service item",
 *     description="Service item",
 *     @OA\Xml(
 *         name="Service"
 *     )
 * )
 */
class ServiceItem
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=4
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *      title="Name",
     *      description="Name of service",
     *      example="кредитование"
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *      title="Alias",
     *      description="Alias of service (unique)",
     *      example="credit"
     * )
     *
     * @var string
     */
    public $alias;
}
