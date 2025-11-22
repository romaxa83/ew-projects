<?php

namespace App\Http\Controllers\V1\Saas\Companies;

use App\Events\BS\Vehicles\ToggleUseBSEvent;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\Companies\CompanyAdminRequest;
use App\Http\Requests\Saas\Companies\CompanyShortlistRequest;
use App\Http\Requests\Saas\Companies\DestroyNoteRequest;
use App\Http\Requests\Saas\Companies\CompanyFilterRequest;
use App\Http\Requests\Saas\Companies\DestroyRequest;
use App\Http\Requests\Saas\Companies\UpdateCompanyRequest;
use App\Http\Resources\Saas\Companies\CompanyDeviceInfoResource;
use App\Http\Resources\Saas\Companies\CompanyPaginatedResource;
use App\Http\Resources\Saas\Companies\CompanyResource;
use App\Http\Resources\Saas\Companies\CompanyShortlistResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Permissions\Saas\Companies\CompanyDelete;
use App\Permissions\Saas\Companies\CompanyList;
use App\Permissions\Saas\Companies\CompanyShow;
use App\Permissions\Saas\Companies\CompanyStatus;
use App\Permissions\Saas\Companies\CompanyUpdate;
use App\Repositories\Saas\Company\CompanyRepository;
use App\Services\Saas\Companies\CompanyService;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CompanyController extends ApiController
{
    protected CompanyRepository $repo;

    public function __construct(
        CompanyRepository $repo
    )
    {
        parent::__construct();
        $this->repo = $repo;
    }

    /**
     * @param CompanyFilterRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/v1/saas/companies", tags={"Companies"},
     *     summary="Returns companies list",
     *     operationId="companies list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="active", in="query", required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="query", in="query", required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="asc", enum ={"asc","desc"})
     *     ),
     *     @OA\Parameter(name="order", in="query", description="Field to sort by", required=false,
     *          @OA\Schema(type="string", default="id", enum ={"name", "created_at", "registration_at"})
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyPaginatedResource")
     *     ),
     * )
     */
    public function index(CompanyFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize(CompanyList::KEY);

        $companies = Company::filter($request->validated())
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return CompanyPaginatedResource::collection($companies);
    }

    /**
     * @param Company $company
     * @return CompanyResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/v1/saas/companies/{companyId}",
     *     tags={"Companies"},
     *     summary="Returns company info",
     *     operationId="company info",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *         name="company",
     *         in="path",
     *         description="The ID of the company",
     *         required=true,
     *         @OA\Schema(
     *               type="integer",
     *               format="int64"
     *          ),
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyResource")
     *     ),
     * )
     */
    public function show(Company $company): CompanyResource
    {
        $this->authorize(CompanyShow::KEY, $company);

        return CompanyResource::make($company);
    }

    /**
     * @param UpdateCompanyRequest $request
     * @param Company $company
     * @return CompanyResource|JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Put(
     *     path="/v1/saas/companies/{companyId}",
     *     tags={"Companies"},
     *     summary="Update company",
     *     operationId="Update company",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Company name", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="address", in="query", description="address", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="city", in="query", description="city", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="state_id", in="query", description="state id", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="zip", in="query", description="zip code", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="timezone", in="query", description="timezone", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="email", in="query", description="email", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="phone", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="use_in_body_shop", in="query", description="Is company can be used is Body Shop", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="next_rate", in="query", description="Rate per device for next billing period", required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyResource")
     *     ),
     * )
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $this->authorize(CompanyUpdate::KEY, $company);

        $useBodyShopOld = $company->use_in_body_shop;

        $company->update($request->validated());

        event(new ToggleUseBSEvent($company, $useBodyShopOld));

        if($request->has('next_rate') && !($company->isExclusivePlan())){
            /** @var $deviceSubscriptionService DeviceSubscriptionService */
            $deviceSubscriptionService = resolve(DeviceSubscriptionService::class);
            $deviceSubscriptionService->setNewRate($company, $request['next_rate']);
        }

        return CompanyResource::make($company);
    }

    /**
     * @param DestroyNoteRequest $companyDeleteRequest
     * @param Company $company
     * @param CompanyService $companyService
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/saas/companies/{companyId}/send-destroy-notification",
     *     tags={"Companies"},
     *     summary="Send notification to backoffice admin with confirm/decline url",
     *     operationId="Send notification to backoffice admin with confirm/decline url",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="company",
     *          in="path",
     *          description="ID company",
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Response(response=204, description="Successful operation"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function sendDestroyNotification(
        DestroyNoteRequest $companyDeleteRequest,
        Company $company,
        CompanyService $companyService
    ): JsonResponse
    {
        try {
            $companyService->sendDestroyTokens($company, $companyDeleteRequest->user());
        } catch (Exception $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param DestroyRequest $request
     * @param CompanyService $companyService
     * @return JsonResponse
     * @throws \Throwable
     *
     * @OA\Post(
     *     path="/v1/saas/companies/company/set-destroy",
     *     tags={"Companies"},
     *     summary="Confirm or decline delete company",
     *     operationId="Confirm or decline delete company",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="type", in="query", required=true, description="Confirm or decline action", @OA\Schema (type="string", enum={"confirm","decline"})),
     *     @OA\Parameter (name="token", in="query", required=true, description="Token action", @OA\Schema (type="string")),
     *     @OA\Response(response=204, description="Successful operation"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function setDestroy(DestroyRequest $request, CompanyService $companyService): JsonResponse
    {
        $requestData = $request->validated();
        /**@var Company $company*/
        $company = $requestData['company'];

        if ($requestData['type'] === 'decline') {
            if ($companyService->declineDestroy($company)) {
                return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
            }
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($companyService->confirmDestroy($company)) {
            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        }
        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param Company $company
     * @return CompanyResource
     * @throws AuthorizationException
     *
     * @OA\Put(path="/v1/saas/companies/{companyId}/status", tags={"Companies"}, summary="Change company status", operationId="Change company status", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyPaginatedResource")
     *     ),
     * )
     */
    public function changeActiveStatus(Company $company): CompanyResource
    {
        $this->authorize(CompanyStatus::KEY, $company);

        $company->toggleActivity();

        return CompanyResource::make($company);
    }

    /**
     * @param Company $company
     * @return CompanyResource
     * @throws AuthorizationException
     *
     * @OA\Delete(path="/v1/saas/companies/{companyId}",
     *     tags={"Companies"},
     *     summary="Delete company",
     *     operationId="Delete company status",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function delete(Company $company): JsonResponse
    {
        $this->authorize(CompanyDelete::KEY, $company);

        try {
            /** @var $service CompanyService */
            $service = resolve(CompanyService::class);

            $service->delete($company);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Throwable $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param CompanyShortlistRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/v1/saas/companies/shortlist", tags={"Companies"},
     *     summary="Returns companies shortlist",
     *     operationId="companies shortlist",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="gps_enabled", in="query", required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="id", in="query", required=false,
     *          @OA\Schema(type="int")
     *     ),
     *     @OA\Parameter(name="query", in="query", required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyShortlistResource")
     *     ),
     * )
     */
    public function shortlist(CompanyShortlistRequest $request): AnonymousResourceCollection
    {
        $this->authorize(CompanyList::KEY);

        $companies = Company::filter($request->validated())->get();

        return CompanyShortlistResource::collection($companies);
    }

    /**
     * @param CompanyShortlistRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(path="/v1/saas/companies/{id}/devices-info", tags={"Companies"},
     *     summary="Returns info about gps devices attached to company",
     *     operationId="company devices info",
     *     deprecated=true,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *            description="ID company",
     *            @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompanyDeviceInfoResource")
     *     ),
     * )
     */
    public function devicesInfo(Request $request, $id, CompanyRepository $repo): CompanyDeviceInfoResource
    {
        $this->authorize(CompanyList::KEY);

        return CompanyDeviceInfoResource::make(
            $repo->getDeviceInfo($id)
        );
    }

    /**
     * @OA\Get(path="/v1/saas/companies/{id}/admins",
     *     tags={"Companies"},
     *     summary="Returns list admin and super-admin for a company",
     *     operationId="ReturnsListAdminAndSuperAdminCompany",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="{id}", in="path", required=true,
     *            description="ID company",
     *            @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Parameter(name="email_search", in="query", required=false,
     *          @OA\Schema(type="string")
     *      ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/UserShortList")
     *     ),
     * )
     */
    public function admins(
        CompanyAdminRequest $request,
        $id
    ): AnonymousResourceCollection
    {
        $this->authorize(CompanyList::KEY);

        return UserShortListResource::collection(
            User::query()
                ->filter($request->validated())
                ->where('carrier_id', $id)
                ->whereHas('roles', function ($b){
                    $b->whereIn('name', [
                        User::SUPERADMIN_ROLE,
                        User::ADMIN_ROLE,
                    ]);
                })
                ->get()
        );
    }
}
