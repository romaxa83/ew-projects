<?php

namespace App\Http\Swagger\Response;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Example response structure for success",
 *     description="Example response structure for success",
 * )
 */
class SuccessResponse
{
    /**
     * @OA\Property(
     *     title="Data (message)",
     *     description="There will be a data or message",
     *     example="[]",
     * )
     *
     * @var array|string
     */
    public $data;

    /**
     * @OA\Property(
     *     title="Success",
     *     description="The value in this field will be 'true'",
     *     example="true",
     * )
     *
     * @var bool
     */
    public bool $success;
}

