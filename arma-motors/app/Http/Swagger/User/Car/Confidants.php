<?php

namespace App\Http\Swagger\User\Car;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Confidant",
 *     description="data for confidant",
 * )
 */
class Confidants
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="",
     *     example="97ed860e-f4f4-11eb-8274-4cd98fc26f15",
     * )
     * @var int
     */
    public string $id;

    /**
     * @OA\Property(
     *     title="name",
     *     description="name",
     *     example="Кравчук Олег Петрович",
     * )
     * @var string
     */
    public string $name;

    /**
     * @OA\Property(
     *     title="number",
     *     description="number",
     *     example="0968381637",
     * )
     * @var string
     */
    public string $number;
}
