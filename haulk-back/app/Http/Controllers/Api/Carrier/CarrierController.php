<?php


namespace App\Http\Controllers\Api\Carrier;


use App\Http\Controllers\ApiController;
use App\Http\Requests\Carrier\CarrierRequest;
use App\Http\Requests\Carrier\InsuranceRequest;
use App\Http\Requests\Carrier\SendDestroyNoteRequest;
use App\Http\Requests\Carrier\SetDestroyRequest;
use App\Http\Requests\Carrier\UploadInfoPhotoRequest;
use App\Http\Requests\Carrier\UploadInsurancePhotoRequest;
use App\Http\Requests\Carrier\UploadUsdotPhotoRequest;
use App\Http\Requests\Carrier\UploadW9PhotoRequest;
use App\Http\Requests\Carrier\CompanySettingsNotificationRequest;
use App\Http\Resources\Carrier\CarrierResource;
use App\Http\Resources\Carrier\InsuranceResource;
use App\Http\Resources\Carrier\UsdotResource;
use App\Http\Resources\Carrier\W9Resource;
use App\Http\Resources\Carrier\CompanySettingsNotificationResource;
use App\Models\Saas\Company\Company;
use App\Models\Saas\Company\CompanyInsuranceInfo;
use App\Services\Billing\BillingService;
use App\Services\Carriers\CarrierService;
use App\Services\Events\Carrier\CarrierEventService;
use App\Services\Events\Carrier\CarrierInsuranceEventService;
use App\Services\Events\EventService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Log;
use Throwable;

class CarrierController extends ApiController
{
    public const MAX_FILE_SIZE = 10 * 1024 * 1024;
    public const ALLOWED_FILE_TYPES = 'pdf,jpeg,bmp,png,img';

    private function preparePhones(array $phones, $with_extension = false): array
    {
        if ($with_extension) {
            $phones = array_filter($phones, function ($el) {
                return (isset($el['number']) || isset($el['extension']));
            });

            $phones = array_values(
                array_map(
                    function ($el) {
                        return [
                            'name' => $el['name'] ?? '',
                            'number' => isset($el['number']) ? preg_replace('/[^0-9]+/', '', $el['number']) : '',
                            'extension' => $el['extension'] ?? '',
                        ];
                    },
                    $phones
                )
            );
        } else {
            $phones = array_filter(
                $phones,
                function ($el) {
                    return (isset($el['number']));
                }
            );

            $phones = array_values(
                array_map(
                    function ($el) {
                        return [
                            'name' => $el['name'] ?? '',
                            'number' => isset($el['number']) ? preg_replace('/[^0-9]+/', '', $el['number']) : '',
                        ];
                    },
                    $phones
                )
            );
        }

        return $phones;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return CarrierResource
     *
     * @OA\Get(
     *     path="/api/carrier",
     *     tags={"Carrier profile"},
     *     summary="Get carrier profile info",
     *     operationId="Get carrier profile data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierProfileResource")
     *     ),
     * )
     */
    public function show(Request $request): CarrierResource
    {
        return CarrierResource::make(
            $request->user()->getCompany()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CarrierRequest $request
     * @return CarrierResource|JsonResponse
     * @throws Throwable
     *
     * @OA\Put(
     *     path="/api/carrier",
     *     tags={"Carrier profile"},
     *     summary="Update carrier profile",
     *     operationId="Update carrier profile",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Carrier name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="ADESA LAS VEGAS",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="usdot",
     *          in="query",
     *          description="Carrier usdot",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="2588963",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="address",
     *          in="query",
     *          description="Carrier address",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="1395 E 4th St, Reno, NV 89512",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="city",
     *          in="query",
     *          description="Carrier city",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Reno",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="state_id",
     *          in="query",
     *          description="Carrier state id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="zip",
     *          in="query",
     *          description="Carrier zip",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="89512",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="timezone",
     *          in="query",
     *          description="Carrier timezone",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="America/Los_Angeles",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          description="Carrier phone",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="1234567",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="phone_name",
     *          in="query",
     *          description="Carrier contact name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="John Doe",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="phone_extension",
     *          in="query",
     *          description="Carrier phone extension",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="phones",
     *          in="query",
     *          description="Carrier phone numbers",
     *          required=false,
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
     *     @OA\Parameter(
     *          name="email",
     *          in="query",
     *          description="Carrier email",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="mail@server.net",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="fax",
     *          in="query",
     *          description="Carrier fax number",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="(248) 721-4985",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="website",
     *          in="query",
     *          description="Carrier site",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="www.company.com",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="billing_phone",
     *          in="query",
     *          description="Carrier billing phone",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="1234567",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="billing_phone_name",
     *          in="query",
     *          description="Carrier billing contact name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="John Doe",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="billing_phone_extension",
     *          in="query",
     *          description="Carrier billing phone extension",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="1234567",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="billing_phones",
     *          in="query",
     *          description="Carrier billing phone numbers",
     *          required=false,
     *          @OA\Schema(
     *              type="array",
     *              description="User aditional contact phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="billing_email",
     *          in="query",
     *          description="Carrier billing email",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="mail@server.net",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="billing_payment_details",
     *          in="query",
     *          description="Carrier payment details",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="Some payment details",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="billing_terms",
     *          in="query",
     *          description="Carrier terms",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="Some carrier terms",
     *          )
     *     ),
     *     @OA\Parameter(name="driver_salary_contact_info",in="query", required=false,
     *         @OA\Schema(type="object", allOf={
     *             @OA\Schema(
     *                 required={"email", "phones"},
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phones", type="array",
     *                     @OA\Items(ref="#/components/schemas/PhonesRaw")
     *                  )
     *              )
     *          })
     *      ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierProfileResource")
     *     ),
     * )
     */
    public function update(CarrierRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            if (isset($validated['phones'])) {
                $validated['phones'] = $this->preparePhones($validated['phones'], true);
            }

            if (isset($validated['billing_phones'])) {
                $validated['billing_phones'] = $this->preparePhones($validated['billing_phones'], true);
            }

            $company = $request->user()->getCompany();
            $company->fill($validated);
            $company->save();

            $billingInfo = $company->billingInfo;
            $billingInfo->fill($validated);
            $billingInfo->save();

            DB::commit();

            EventService::carrier($company)
                ->user($request->user())
                ->update()
                ->broadcast();

            return CarrierResource::make($company);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param UploadInfoPhotoRequest $request
     * @return CarrierResource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/carrier/info/upload-photo",
     *     tags={"Carrier profile"},
     *     summary="Upload carrier profile photo",
     *     operationId="Upload carrier profile photo",
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
     *         @OA\JsonContent(ref="#/components/schemas/CarrierProfileResource")
     *     ),
     * )
     *
     */
    public function uploadInfoPhoto(UploadInfoPhotoRequest $request): CarrierResource
    {
        $this->authorize('company-settings update');

        $company = $request->user()->getCompany();
        $company->addMediaWithRandomName(
            Company::LOGO_FIELD_CARRIER,
            $request->file(Company::LOGO_FIELD_CARRIER),
            true
        );

        EventService::carrier($company)
            ->user($request->user())
            ->update(CarrierEventService::ACTION_PREFIX_ADD_PHOTO)
            ->broadcast();

        return CarrierResource::make($company);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/carrier/info/delete-photo",
     *     tags={"Carrier profile"},
     *     summary="Delete сarrier profile photo",
     *     operationId="Delete сarrier profile photo",
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
        $this->authorize('company-settings update');

        $company = $request->user()->getCompany();
        $company->clearMediaCollection(Company::LOGO_FIELD_CARRIER);

        EventService::carrier($company)
            ->user($request->user())
            ->update(CarrierEventService::ACTION_PREFIX_DELETE_PHOTO)
            ->broadcast();

        return (
            CarrierResource::make($company)
        )
            ->response()
            ->setStatusCode(204);
    }

    /**
     * @param UploadW9PhotoRequest $request
     * @return W9Resource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/carrier/w9/upload-photo",
     *     tags={"Carrier profile"},
     *     summary="Upload carrier profile w9 photo",
     *     operationId="Upload carrier profile w9 photo",
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
     *                      property="w9_form_image",
     *                      type="string",
     *                      format="binary",
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierW9Resource")
     *     ),
     * )
     */
    public function uploadW9Photo(UploadW9PhotoRequest $request): W9Resource
    {
        $this->authorize('company-settings update');

        $company = $request->user()->getCompany();
        $company->addMediaWithRandomName(
            Company::W9_FIELD_CARRIER,
            $request->file(Company::W9_FIELD_CARRIER),
            true
        );

        EventService::carrier($company)
            ->user($request->user())
            ->update(CarrierEventService::ACTION_PREFIX_ADD_W9)
            ->broadcast();

        return W9Resource::make($company);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/carrier/w9/delete-photo",
     *     tags={"Carrier profile"},
     *     summary="Delete сarrier profile w9 photo",
     *     operationId="Delete сarrier profile w9 photo",
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
    public function deleteW9Photo(Request $request): JsonResponse
    {
        $this->authorize('company-settings update');

        $company = $request->user()->getCompany();
        $company->clearMediaCollection(Company::W9_FIELD_CARRIER);

        EventService::carrier($company)
            ->user($request->user())
            ->update(CarrierEventService::ACTION_PREFIX_DELETE_W9)
            ->broadcast();

        return (
            W9Resource::make($company)
        )
            ->response()
            ->setStatusCode(204);
    }

    /**
     * @param Request $request
     * @return W9Resource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/carrier/w9/get-photo",
     *     tags={"Carrier profile"},
     *     summary="Get сarrier profile w9 photo",
     *     operationId="Get сarrier profile w9 photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierW9Resource")
     *     ),
     * )
     */
    public function getW9Photo(Request $request): W9Resource
    {
        $this->authorize('company-settings read');

        $company = $request->user()->getCompany();

        return W9Resource::make($company);
    }

    /**
     * @param UploadUsdotPhotoRequest $request
     * @return UsdotResource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/carrier/usdot/upload-photo",
     *     tags={"Carrier profile"},
     *     summary="Upload carrier profile usdot photo",
     *     operationId="Upload carrier profile usdot photo",
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
     *                      property="usdot_number_image",
     *                      type="string",
     *                      format="binary",
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierUsdotResource")
     *     ),
     * )
     */
    public function uploadUsdotPhoto(UploadUsdotPhotoRequest $request): UsdotResource
    {
        $this->authorize('company-settings update');

        $company = $request->user()->getCompany();
        $company->addMediaWithRandomName(
            Company::USDOT_FIELD_CARRIER,
            $request->file(Company::USDOT_FIELD_CARRIER),
            true
        );

        EventService::carrier($company)
            ->user($request->user())
            ->update(CarrierEventService::ACTION_PREFIX_ADD_USDOT)
            ->broadcast();

        return UsdotResource::make($company);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/carrier/usdot/delete-photo",
     *     tags={"Carrier profile"},
     *     summary="Delete сarrier profile usdot photo",
     *     operationId="Delete сarrier profile usdot photo",
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
    public function deleteUsdotPhoto(Request $request): JsonResponse
    {
        $this->authorize('company-settings update');

        $company = $request->user()->getCompany();
        $company->clearMediaCollection(Company::USDOT_FIELD_CARRIER);

        EventService::carrier($company)
            ->user($request->user())
            ->update(CarrierEventService::ACTION_PREFIX_DELETE_USDOT)
            ->broadcast();

        return (
            UsdotResource::make($company)
        )
            ->response()
            ->setStatusCode(204);
    }

    /**
     * @param Request $request
     * @return UsdotResource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/carrier/usdot/get-photo",
     *     tags={"Carrier profile"},
     *     summary="Get сarrier profile usdot photo",
     *     operationId="Get сarrier profile usdot photo",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierUsdotResource")
     *     ),
     * )
     */
    public function getUsdotPhoto(Request $request): UsdotResource
    {
        $this->authorize('company-settings read');

        $company = $request->user()->getCompany();

        return UsdotResource::make($company);
    }

    /**
     * @param Request $request
     * @return InsuranceResource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/carrier/insurance",
     *     tags={"Carrier profile"},
     *     summary="Get сarrier profile insurance",
     *     operationId="Get сarrier profile insurance",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierInsuranceResource")
     *     ),
     * )
     */
    public function getInsurance(Request $request): InsuranceResource
    {
        $this->authorize('company-settings read');

        $insuranceInfo = $request->user()->getCompany()->insuranceInfo;

        return InsuranceResource::make($insuranceInfo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param InsuranceRequest $request
     * @return InsuranceResource|JsonResponse
     * @throws Throwable
     *
     * @OA\Put(
     *     path="/api/carrier/insurance",
     *     tags={"Carrier profile"},
     *     summary="Update carrier profile insurance",
     *     operationId="Update carrier profile insurance",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="insurance_expiration_date",
     *          in="query",
     *          description="Insurance expiration date",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="25.10.2020",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="insurance_cargo_limit",
     *          in="query",
     *          description="Insurance cargo limit",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="2588963",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="insurance_deductible",
     *          in="query",
     *          description="Insurance deductible",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1395",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="insurance_agent_name",
     *          in="query",
     *          description="Insurance agent name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="John Doe",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="insurance_agent_phone",
     *          in="query",
     *          description="Insurance agent phone",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="1231212",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierInsuranceResource")
     *     ),
     * )
     */
    public function updateInsurance(InsuranceRequest $request)
    {
        $this->authorize('company-settings update');

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $insuranceInfo = $request->user()->getCompany()->insuranceInfo;
            $insuranceInfo->fill($validated);
            $insuranceInfo->save();

            DB::commit();

            EventService::carrierInsurance($insuranceInfo)
                ->user($request->user())
                ->update()
                ->broadcast();

            return InsuranceResource::make($insuranceInfo);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param UploadInsurancePhotoRequest $request
     * @return InsuranceResource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/carrier/insurance/upload-photo",
     *     tags={"Carrier profile"},
     *     summary="Upload carrier profile insurance photo",
     *     operationId="Upload carrier profile insurance photo",
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
     *                      property="insurance_certificate_image",
     *                      type="string",
     *                      format="binary",
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CarrierInsuranceResource")
     *     ),
     * )
     */
    public function uploadInsurancePhoto(UploadInsurancePhotoRequest $request): InsuranceResource
    {
        $this->authorize('company-settings update');

        $insuranceInfo = $request->user()->getCompany()->insuranceInfo;
        $insuranceInfo->addMediaWithRandomName(
            CompanyInsuranceInfo::INSURANCE_FIELD_CARRIER,
            $request->file(CompanyInsuranceInfo::INSURANCE_FIELD_CARRIER),
            true
        );

        EventService::carrierInsurance($insuranceInfo)
            ->user($request->user())
            ->update(CarrierInsuranceEventService::ACTION_PREFIX_ADD_PHOTO)
            ->broadcast();

        return InsuranceResource::make($insuranceInfo);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/carrier/insurance/delete-photo",
     *     tags={"Carrier profile"},
     *     summary="Delete сarrier insurance photo",
     *     operationId="Delete сarrier insurance photo",
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
    public function deleteInsurancePhoto(Request $request): JsonResponse
    {
        $this->authorize('company-settings update');

        $insuranceInfo = $request->user()->getCompany()->insuranceInfo;
        $insuranceInfo->clearMediaCollection(CompanyInsuranceInfo::INSURANCE_FIELD_CARRIER);

        EventService::carrierInsurance($insuranceInfo)
            ->user($request->user())
            ->update(CarrierInsuranceEventService::ACTION_PREFIX_DELETE_PHOTO)
            ->broadcast();

        return (
            InsuranceResource::make($insuranceInfo)
        )
            ->response()
            ->setStatusCode(204);
    }

    /**
     * Display notifications settings
     *
     * @param Request $request
     * @return CompanySettingsNotificationResource
     * @throws AuthorizationException
     *
     * @OA\Get(path="/api/carrier/notification", tags={"Carrier profile"}, summary="Display notifications settings",
     *     operationId="Display notifications settings", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanySettingsNotificationResource")
     *     ),
     * )
     */
    public function getNotificationSettings(Request $request): CompanySettingsNotificationResource
    {
        $this->authorize('company-settings read');

        $notificationSettings = $request->user()->getCompany()->notificationSettings;

        return CompanySettingsNotificationResource::make($notificationSettings);
    }

    /**
     * Update notifications settings
     *
     * @param CompanySettingsNotificationRequest $request
     * @return CompanySettingsNotificationResource|JsonResponse
     * @throws Throwable
     *
     * @OA\Put(path="/api/carrier/notification",
     *     tags={"Carrier profile"},
     *     summary="Update notifications settings",
     *     operationId="Update notifications settings",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="notification_emails", in="query", description="Receive pickup/delivery notifications. Enter multiple emails with comma.", required=false,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="value", type="string", description="email"),
     *                      )
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Parameter(name="receive_bol_copy_emails", in="query", description="Receive copy of BOLs for all delivered loads. Enter multiple emails with comma", required=false,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="value", type="string", description="email"),
     *                      )
     *                  }
     *              )
     *          )
     *     ),
     *     @OA\Parameter(name="brokers_delivery_notification", in="query", description="Receive copy of BOLs for all delivered loads. Enter multiple emails with comma", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="add_pickup_delivery_dates_to_bol", in="query", description="Receive copy of BOLs for all delivered loads. Enter multiple emails with comma", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="send_bol_invoice_automatically", in="query", description="Receive copy of BOLs for all delivered loads. Enter multiple emails with comma", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Parameter(name="is_invoice_allowed", in="query", description="Есть у водителей возможность отправки invoice", required=false,
     *          @OA\Schema(type="boolean",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanySettingsNotificationResource")
     *     ),
     * )
     */
    public function updateNotificationSettings(CompanySettingsNotificationRequest $request)
    {
        $this->authorize('company-settings update');

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $notificationSettings = $request->user()->getCompany()->notificationSettings;
            $notificationSettings->fill($validated);
            $notificationSettings->save();

            DB::commit();

            EventService::carrierNotification($notificationSettings)
                ->user($request->user())
                ->update()
                ->broadcast();

            return CompanySettingsNotificationResource::make($notificationSettings);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SendDestroyNoteRequest $request
     * @param CarrierService $carrierService
     * @param BillingService $billingService
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Post(
     *     path="/api/carrier/send-destroy-notification",
     *     tags={"Carrier profile"},
     *     summary="Send a notification to the user to confirm the action",
     *     operationId="Send a notification to the user to confirm the action",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response (response=200, description="Successful sending notification"),
     *     @OA\Response (response=422, description="Validate error"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=403, description="Forbiden")
     * )
     */
    public function sendDestroyNotification(SendDestroyNoteRequest $request, CarrierService $carrierService, BillingService $billingService): JsonResponse
    {
        $company = $request->validated()['company'];

        try {
            $billingService->unsubscribe($company, $request->user());

            $carrierService->sendDestroyNote($company);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->makeSuccessResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SetDestroyRequest $request
     * @param CarrierService $carrierService
     * @return JsonResponse
     * @throws Throwable
     *
     * @OA\Post(
     *     path="/api/carrier/set-destroy",
     *     tags={"Carrier profile"},
     *     summary="Confirm/decline destroy company",
     *     operationId="Confirm/decline destroy company",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter (name="type", in="query", required=true, description="Confirm or decline action", @OA\Schema (type="string", enum={"confirm","decline"})),
     *     @OA\Parameter (name="token", in="query", required=true, description="Token action", @OA\Schema (type="string")),
     *     @OA\Response (response=200, description="Successful confirm"),
     *     @OA\Response (response=204, description="Success decline"),
     *     @OA\Response (response=422, description="Validate error"),
     *     @OA\Response (response=402, description="Payment required"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=403, description="Forbiden")
     * )
     */
    public function setDestroy(
        SetDestroyRequest $request,
        CarrierService $carrierService
    ): JsonResponse
    {
        $tokenType = $request->validated()['type'];
        $company = $request->user()->getCompany();

        try {
            if ($tokenType === 'confirm') {
                $carrierService->confirmDestroy($company);

                EventService::carrier($company)
                    ->user($request->user())
                    ->delete()
                    ->broadcast();

            } else {
                $carrierService->declineDestroy($company);
            }

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->makeSuccessResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
