<?php

namespace App\Resources\Swagger\Statistic;

/**
 * @OA\Schema(type="object", title="Report Size Response",
 *     @OA\Property(property="countries", type="array", description="Данные по странам", @OA\Items(
 *         @OA\Property(property="name", type="string", description="Страна (национальность)", example="French"),
 *         @OA\Property(property="dealers_count", type="integer", description="Кол-во дилеров по этой стране", example=2),
 *         @OA\Property(property="data", type="array", description="Данные", @OA\Items(
 *             @OA\Property(property="name", type="string", description="Название модели (modelDescription)", example="AT FACTORY INSTALLED"),
 *             @OA\Property(property="size_name", type="integer", description="Значения size", example=266),
 *             @OA\Property(property="id", type="integer", description="JD ID модели", example=250),
 *             @OA\Property(property="eg", type="object", description="Значения по equipment group",
 *                 @OA\Property(property="id", type="integer", description="ID equipment group", example=5),
 *                 @OA\Property(property="name", type="string", description="Название equipment group", example="AMS")
 *             ),
 *             @OA\Property(property="values", type="array", @OA\Items(
 *                     @OA\Property(property="dealer_name", type="string", description="Название дилера", example="ETS VROMMAN"),
 *                     @OA\Property(property="value", type="integer", description="Кол-во моделей по данному дилеру", example=3),
 *                 )
 *             ),
 *         )),
 *     )),
 * )
 */
class ReportSizeResponse
{}
