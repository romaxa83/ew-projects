<?php

namespace App\Resources\Swagger;

/**
 * @OA\Schema(type="object", title="Success with simple data",
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="key_1", type="string", example="value_1"),
 *         @OA\Property(property="key_2", type="string", example="value_2"),
 *         @OA\Property(property="key_n", type="string", example="value_n"),
 *     ),
 *     @OA\Property(property="success", type="boolean", example=true),
 * )
 */
class SuccessWithSimpleData
{}
