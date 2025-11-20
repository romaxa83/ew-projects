<?php

namespace App\Resources\Swagger;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Example response structure for error",
 *     description="Example response structure for error",
 * )
 */
class ErrorResponse
{
    /**
     * @OA\Property(
     *     title="Data (message)",
     *     description="There will be a description of the error",
     *     example="Message",
     * )
     *
     * @var string
     */
    public string $data;

    /**
     * @OA\Property(
     *     title="Success",
     *     description="The value in this field will be 'false'",
     *     example="false",
     * )
     *
     * @var bool
     */
    public bool $success;
}
