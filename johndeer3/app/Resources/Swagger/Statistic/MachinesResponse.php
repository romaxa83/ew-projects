<?php

namespace App\Resources\Swagger\Statistic;

/**
 * @OA\Schema(type="object", title="Machines Response",
 *     @OA\Property(property="dealer", type="string", description="Имя дилера", example="Agristar"),
 *     @OA\Property(property="values", type="array", description="Значения", @OA\Items(
 *         @OA\Property(property="name", type="string", description="Название модели (modelDescription)", example="VECTOR 410"),
 *         @OA\Property(property="model_description_id", type="integer", description="ID modelDescription", example="159"),
 *         @OA\Property(property="data", type="array", description="Данные по характеристики", @OA\Items(
 *             @OA\Property(property="name", type="string", description="Название характеристики", example="Скорость"),
 *             @OA\Property(property="feature_id", type="integer", description="ID характеристики", example=250),
 *             @OA\Property(property="value", type="object", description="Значения по характеристикам",
 *                 @OA\Property(property="count", type="integer", description="Кол-во значений", example=5),
 *                 @OA\Property(property="avg", type="integer", description="Среднее арифметическое этих значений", example=138)
 *             ),
 *         )),
 *     )),
 * )
 */
class MachinesResponse
{}

