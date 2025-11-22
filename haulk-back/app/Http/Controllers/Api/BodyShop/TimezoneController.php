<?php


namespace App\Http\Controllers\Api\BodyShop;


class TimezoneController extends \App\Http\Controllers\Api\TimezoneController
{
    /**
     *
     * @OA\Get(
     *     path="/api/body-shop/timezone-list",
     *     tags={"Timezones Body Shop"},
     *     summary="Get timezone list",
     *     operationId="Get timezone list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TimezoneListResource")
     *     ),
     * )
     */

}
