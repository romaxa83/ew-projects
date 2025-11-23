<?php

namespace WezomCms\Core\Api\Swagger;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Example response structure for auth tokens",
 *     description="Example response structure for success",
 * )
 */
class TokensResponse
{
    /**
     * @OA\Property(
     *     title="Tokens",
     *     @OA\Schema(ref="#/components/schemas/Tokens")
     * )
     *
     * @var Tokens
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

    /**
     * @OA\Property(
     *     title="Code",
     *     description="Inner error code",
     *     example=0,
     * )
     *
     * @var int
     */
    public bool $code;
}
