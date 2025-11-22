<?php

namespace App\Http\Swagger\Catalog;

/**
 * @OA\Schema(
 *     title="DealershipResource",
 *     description="Dealership resource",
 *     @OA\Xml(name="DealershipResource")
 * )
 */
class DealershipResource
{
    /**
     * @OA\Property(
     *     title="Data",
     *     description="Data wrapper"
     * )
     *
     * @var Dealership[]
     */
    private $data;
}
