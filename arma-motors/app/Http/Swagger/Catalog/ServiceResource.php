<?php

namespace App\Http\Swagger\Catalog;

/**
 * @OA\Schema(
 *     title="ServiceResource",
 *     description="Service resource",
 *     @OA\Xml(name="ServiceResource")
 * )
 */
class ServiceResource
{
    /**
     * @OA\Property(
     *     title="Data",
     *     description="Data wrapper"
     * )
     *
     * @var Service[]
     */
    private $data;
}
