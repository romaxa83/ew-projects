<?php

namespace App\Resources\Swagger;

/**
 * @OA\Schema(type="object", title="Link",
 *     @OA\Property(property="first", type="string", example = "http://192.168.144.1/site/?page=1"),
 *     @OA\Property(property="last", type="string", example = "http://192.168.144.1/site/?page=20"),
 *     @OA\Property(property="prev", type="string", example = "http://192.168.144.1/site/?page=2"),
 *     @OA\Property(property="next", type="string", example = "http://192.168.144.1/site/?page=4"),
 * )
 */
class Link
{}
