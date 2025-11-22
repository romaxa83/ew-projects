<?php

namespace App\Http\Swagger\Catalog;

/**
 * @OA\Schema(
 *     title="Dealership",
 *     description="Dealership",
 *     @OA\Xml(name="Dealership")
 * )
 */
class Dealership
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *      title="Name",
     *      description="Name of dealership",
     *      example="Арма Моторс"
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *      title="Alias",
     *      description="Alias of dealership",
     *      example="arma-motors-mitsubishi"
     * )
     *
     * @var string
     */
    public $alias;
}
