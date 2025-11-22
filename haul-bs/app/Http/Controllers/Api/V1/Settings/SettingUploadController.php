<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Events\Events\Settings\RequestToEcom;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Common\UploadLogoRequest;
use App\Http\Resources\Settings\SettingsInfoResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Models\Settings\Settings;
use App\Repositories\Settings\SettingRepository;
use App\Services\Settings\SettingService;

class SettingUploadController extends ApiController
{
    public function __construct(
        protected SettingRepository $repo,
        protected SettingService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/settings/info/upload-logo",
     *     tags={"Settings"},
     *     security={{"Basic": {}}},
     *     summary="Setting Upload logo",
     *     operationId="SettingUploadLogo",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="logo", type="string", format="binary",)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Settings data",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function upload(UploadLogoRequest $request): SettingsInfoResource
    {
        $this->authorize(Permission\Setting\SettingUpdatePermission::KEY);

        $this->service->uploadLogo($request->logo);

        event(new RequestToEcom($this->repo->getInfoForEcomm()));

        return SettingsInfoResource::make(
            $this->repo->getInfo()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/settings/info/upload-ecommerce-logo",
     *     tags={"Settings"},
     *     security={{"Basic": {}}},
     *     summary="Setting Upload ecommerce logo",
     *     operationId="SettingUploadEcommLogo",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="logo", type="string", format="binary",)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Settings data",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function uploadEcommLogo(UploadLogoRequest $request): SettingsInfoResource
    {
        $this->authorize(Permission\Setting\SettingUpdatePermission::KEY);

        $this->service->uploadLogo($request->logo, Settings::ECOMM_LOGO_FIELD);

        event(new RequestToEcom($this->repo->getInfoForEcomm()));

        return SettingsInfoResource::make(
            $this->repo->getInfo()
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/settings/info/delete-logo",
     *     tags={"Settings"},
     *     security={{"Basic": {}}},
     *     summary="Settings Delete logo",
     *     operationId="SettingsDeleteLogo",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete(): SettingsInfoResource
    {
        $this->authorize(Permission\Setting\SettingUpdatePermission::KEY);

        $this->service->deleteLogo();

        event(new RequestToEcom($this->repo->getInfoForEcomm()));

        return SettingsInfoResource::make(
            $this->repo->getInfo()
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/settings/info/delete-ecommerce-logo",
     *     tags={"Settings"},
     *     security={{"Basic": {}}},
     *     summary="Settings Delete ecommerce logo",
     *     operationId="SettingsDeleteEcommLogo",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function deleteEcomm(): SettingsInfoResource
    {
        $this->authorize(Permission\Setting\SettingUpdatePermission::KEY);

        $this->service->deleteLogo(Settings::ECOMM_LOGO_FIELD);

        event(new RequestToEcom($this->repo->getInfoForEcomm()));

        return SettingsInfoResource::make(
            $this->repo->getInfo()
        );
    }
}
