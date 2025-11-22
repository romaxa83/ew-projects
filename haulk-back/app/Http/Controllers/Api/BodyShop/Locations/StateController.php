<?php

namespace App\Http\Controllers\Api\BodyShop\Locations;

class StateController extends \App\Http\Controllers\Api\Locations\StateController
{
    /**
     *
     * @OA\Get(
     *     path="/api/body-shop/states-list",
     *     tags={"States Body Shop"},
     *     summary="Get states list without pagination",
     *     operationId="Get states data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Scope for filter by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="California",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/StateList")
     *     ),
     * )
     */
}
