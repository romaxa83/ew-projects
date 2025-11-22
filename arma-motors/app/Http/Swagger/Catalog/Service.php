<?php

namespace App\Http\Swagger\Catalog;

/**
 * @OA\Schema(
 *     title="Service",
 *     description="Service",
 *     @OA\Xml(
 *         name="Service"
 *     )
 * )
 */
class Service
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

    /**
     * @OA\Property(
     *     title="Parent service",
     *     @OA\Schema(ref="#/components/schemas/ServiceItem")
     * )
     *
     * @var ServiceItem
     */
    public $parent;

    /**
     * @OA\Property(
     *     title="Children service",
     *     @OA\Schema(ref="#/components/schemas/ServiceItem")
     * )
     *
     * @var ServiceItem[]
     */
    public $children;


}
