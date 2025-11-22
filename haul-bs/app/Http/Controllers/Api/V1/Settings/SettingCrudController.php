<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Events\Events\Settings\RequestToEcom;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Settings\SettingsInfoRequest;
use App\Http\Resources\Settings\SettingsInfoResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Repositories\Settings\SettingRepository;
use App\Services\Settings\SettingService;
use Illuminate\Http\Request;

class SettingCrudController extends ApiController
{
    public function __construct(
        protected SettingRepository $repo,
        protected SettingService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/settings/info",
     *     tags={"Settings"},
     *     security={{"Basic": {}}},
     *     summary="Get settings data",
     *     operationId="GetSettingsData",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Settings data",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function information(Request $request): SettingsInfoResource
    {
        $this->authorize(Permission\Setting\SettingReadPermission::KEY);

        return SettingsInfoResource::make(
            $this->repo->getInfo()
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/settings/info",
     *     tags={"Settings"},
     *     security={{"Basic": {}}},
     *     summary="Update settings",
     *     operationId="UpdateSettings",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Settings data",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(SettingsInfoRequest $request): SettingsInfoResource
    {
        $this->authorize(Permission\Setting\SettingUpdatePermission::KEY);

        $this->service->update($request->validated());

        event(new RequestToEcom($this->repo->getInfoForEcomm()));

        return SettingsInfoResource::make(
            $this->repo->getInfo()
        );
    }
}
