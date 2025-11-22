<?php

namespace App\Http\Controllers\Api\BodyShop\Locations;

class CityController extends \App\Http\Controllers\Api\Locations\CityController
{
    /**
     *
     * @OA\Get(
     *     path="/api/body-shop/city-autocomplete",
     *     tags={"Cities Body Shop"},
     *     summary="Get city-state-zip autocomplete list",
     *     operationId="Get city-state-zip autocomplete list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="s",
     *          in="query",
     *          description="Scope for autocomplete by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="Los-Angeles",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="zip",
     *          in="query",
     *          description="Scope for autocomplete by zip",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="12345",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CityAutocomplete")
     *     ),
     * )
     */
}
