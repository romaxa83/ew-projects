<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Settings\SettingsEcomInfoResource;
use App\Repositories\Settings\SettingRepository;
use Illuminate\Http\Request;

class EComController extends ApiController
{
    public function __construct(
        protected SettingRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/e-comm/settings",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get all settings for e-comm",
     *     operationId="GetSettingsListForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Settings data",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(Request $request): SettingsEcomInfoResource
    {
        return SettingsEcomInfoResource::make(
            $this->repo->getInfoForEcomm()
        );
    }
}
