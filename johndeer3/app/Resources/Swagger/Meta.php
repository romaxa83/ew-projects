<?php

namespace App\Resources\Swagger;

/**
 * @OA\Schema(type="object", title="Meta",
 *     @OA\Property(property="current_page", type="integer", example=2),
 *     @OA\Property(property="from", type="integer", example=25),
 *     @OA\Property(property="last_page", type="integer", example=125),
 *     @OA\Property(property="path", type="string", example=125),
 *     @OA\Property(property="per_page", type="integer", example=2),
 *     @OA\Property(property="to", type="integer", example=2),
 *     @OA\Property(property="total", type="integer", example=256),
 *     @OA\Property(property="links", type="array", @OA\Items(
 *          ref="#/components/schemas/MetaLink"
 *     )),
 * )
 */
class Meta
{}

