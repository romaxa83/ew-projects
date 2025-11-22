<?php

namespace App\Http\Controllers\Api\BodyShop\Settings;

use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Settings\SettingsInformationRequest;
use App\Http\Requests\BodyShop\Settings\UploadInfoPhotoRequest;
use App\Http\Resources\BodyShop\Settings\SettingsInfoResource;
use App\Models\BodyShop\Settings\Settings;
use App\Services\BodyShop\Settings\SettingsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class SettingsController extends ApiController
{
    private SettingsService $service;

    public function __construct(SettingsService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param Request $request
     * @return SettingsInfoResource|JsonResponse
     *
     * @OA\Get(
     *     path="/api/body-shop/settings/info",
     *     tags={"Settings Body Shop"},
     *     summary="Get settings data",
     *     operationId="Get settings data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     * )
     */
    public function information(Request $request): SettingsInfoResource
    {
        $this->authorize('settings-bs read');

        $settings = $this->service->getInfo();

        return SettingsInfoResource::make($settings);
    }

    /**
     * @param SettingsInformationRequest $request
     * @return SettingsInfoResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Put(path="/api/body-shop/settings/info", tags={"Settings Body Shop"},
     *     summary="Update Settings",
     *     operationId="Update Owner", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="company_name", in="query", description="Company ame", required=true,
     *          @OA\Schema( type="string", default="ADESA LAS VEGAS",)
     *     ),
     *     @OA\Parameter(name="address", in="query", description="Фddress", required=true,
     *          @OA\Schema(type="string", default="1395 E 4th St, Reno, NV 89512",)
     *     ),
     *     @OA\Parameter(name="city", in="query", description="Carrier city",required=true,
     *          @OA\Schema(type="string", default="Reno",)
     *     ),
     *     @OA\Parameter(name="state_id", in="query", description="State id", required=true,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="zip", in="query", description="Zip", required=true,
     *          @OA\Schema(type="string", default="89512",)
     *     ),
     *     @OA\Parameter(name="timezone", in="query", description="Timezone", required=false,
     *          @OA\Schema(type="string", default="America/Los_Angeles",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="Phone", required=true,
     *          @OA\Schema( type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phone_name", in="query", description="Contact name", required=false,
     *          @OA\Schema(type="string", default="John Doe",)
     *     ),
     *     @OA\Parameter(name="phone_extension", in="query", description="Phone extension", required=false,
     *          @OA\Schema(type="string", default="",)
     *     ),
     *     @OA\Parameter(name="phones", in="query", description="Phone numbers", required=false,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="name", type="string", description="Contact person name"),
     *                          @OA\Property(property="number", type="string", description="Phone number"),
     *                          @OA\Property(property="extension", type="string", description="Phone extension"),
     *                      )
     *                  }
     *              ),
     *          )
     *     ),
     *     @OA\Parameter(name="email", in="query", description="Email", required=true,
     *          @OA\Schema(type="string", default="mail@server.net",)
     *     ),
     *     @OA\Parameter(name="fax", in="query", description="Fax number", required=false,
     *          @OA\Schema(type="string", default="(248) 721-4985",)
     *     ),
     *     @OA\Parameter(name="website", in="query", description="Site",required=false,
     *          @OA\Schema(type="string", default="www.company.com",)
     *     ),
     *     @OA\Parameter(name="billing_phone", in="query", description="Billing phone", required=true,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="billing_phone_name", in="query", description="Billing contact name", required=false,
     *          @OA\Schema(type="string",default="John Doe",)
     *     ),
     *     @OA\Parameter(name="billing_phone_extension", in="query", description="Billing phone extension", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="billing_phones", in="query", description="Billing phone numbers", required=false,
     *          @OA\Schema(type="array", description="User aditional contact phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Parameter(name="billing_email", in="query", description="Billing email", required=false,
     *          @OA\Schema(type="string", default="mail@server.net",)
     *     ),
     *     @OA\Parameter(name="billing_payment_details", in="query", description="Payment details", required=false,
     *          @OA\Schema(type="string", default="Some payment details",)
     *     ),
     *     @OA\Parameter(name="billing_terms", in="query", description="Carrier terms", required=false,
     *          @OA\Schema(type="string",default="Some carrier terms",)
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     * )
     *
     */
    public function informationUpdate(SettingsInformationRequest $request)
    {
        $this->authorize('settings-bs update');

        try {
            $settings = $this->service->update($request->validated());

            return SettingsInfoResource::make($settings);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UploadInfoPhotoRequest $request
     * @return SettingsInfoResource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/body-shoop/settings/info/upload-photo",
     *     tags={"Settings Body Shop"},
     *     summary="Upload profile photo",
     *     operationId="Upload profile photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="logo",
     *                      type="string",
     *                      format="binary",
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SettingsInfoResource")
     *     ),
     * )
     *
     */
    public function uploadInfoPhoto(UploadInfoPhotoRequest $request): SettingsInfoResource
    {
        $this->authorize('settings-bs update');

        $this->service->addAttachment(Settings::LOGO_FIELD, $request->file(Settings::LOGO_FIELD));

        return SettingsInfoResource::make($this->service->getInfo());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/body-shop/settings/info/delete-photo",
     *     tags={"Settings Body Shop"},
     *     summary="Delete profile photo",
     *     operationId="Delete profile photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function deleteInfoPhoto(Request $request): JsonResponse
    {
        $this->authorize('settings-bs update');

        $this->service->deleteAttachment(Settings::LOGO_FIELD);

        return (SettingsInfoResource::make($this->service->getInfo()))
            ->response()
            ->setStatusCode(204);
    }
}
