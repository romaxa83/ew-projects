<?php

namespace App\Resources\Swagger;

/**
 * @OA\Schema(type="object", title="Success with simple data",
 *     @OA\Property(property="data", type="string", description="Сообщение", example="done"),
 *     @OA\Property(property="success", type="boolean", example=true),
 * )
 */
class SuccessMessageResponse
{}
