<?php

namespace App\Http\Controllers\V1\Saas\Companies;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\CompanyRegistration\CompanyRegistrationListRequest;
use App\Http\Requests\Saas\CompanyRegistration\CompanyRegistrationRequest;
use App\Http\Resources\Saas\CompanyRegistration\CompanyRegistrationPaginatedResource;
use App\Http\Resources\Saas\CompanyRegistration\CompanyRegistrationResource;
use App\Models\Admins\Admin;
use App\Models\Saas\Company\Company;
use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use App\Notifications\Saas\CompanyRegistration\CompanyRegistrationApproveCongratulations;
use App\Notifications\Saas\CompanyRegistration\CompanyRegistrationConfirmEmail;
use App\Notifications\Saas\CompanyRegistration\CompanyRegistrationRequestApprove;
use App\Notifications\Saas\CompanyRegistration\CompanyRegistrationRequestDecline;
use App\Notifications\Saas\CompanyRegistration\ConfirmRegistration;
use App\Notifications\Saas\CompanyRegistration\NewCompanyRegistration;
use App\Permissions\Saas\CompanyRegistration\CompanyRegistrationApprove;
use App\Permissions\Saas\CompanyRegistration\CompanyRegistrationDecline;
use App\Permissions\Saas\CompanyRegistration\CompanyRegistrationList;
use App\Permissions\Saas\CompanyRegistration\CompanyRegistrationShow;
use App\Services\Saas\Companies\CompanyService;
use App\Services\Usdot\UsdotService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Notification;
use Str;
use Throwable;

class CompanyRegistrationController extends ApiController
{
    /**
     * @param CompanyRegistrationRequest $request
     * @param UsdotService $usdotService
     * @return JsonResponse
     * @throws Exception
     */
    public function registrationRequest(
        CompanyRegistrationRequest $request,
        UsdotService $usdotService
    ): JsonResponse
    {
        try {
            DB::beginTransaction();

            $companyRegistration = CompanyRegistration::where(
                'email',
                $request->input('email')
            )
                ->first();

            $confirmation_hash = Str::random(60);

            if ($companyRegistration && $companyRegistration->isConfirmed()) {
                return $this->makeErrorResponse(
                    trans('You already have a registration request sent, please wait until we approve it.'),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($companyRegistration) {
                $companyRegistration->confirmation_hash = hash('sha256', $confirmation_hash);
                $companyRegistration->save();
            } else {

                $companyRegistration = new CompanyRegistration(
                    $usdotService
                        ->getCarrierInfoByUsdot($request->input('usdot'))
                        ->toArray()
                    + $request->validated()
                );
                $companyRegistration->password = bcrypt($request->input('password'));
                $companyRegistration->confirmation_hash = hash('sha256', $confirmation_hash);
                $companyRegistration->save();
            }

            $companyRegistration->notify(new CompanyRegistrationApproveCongratulations());
            $companyRegistration->notify(new CompanyRegistrationConfirmEmail($confirmation_hash));

            DB::commit();

            return $this->makeSuccessResponse($confirmation_hash);
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error($exception);

            return $this->makeErrorResponse(
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function confirmRegistrationEmail(Request $request, CompanyService $companyService): JsonResponse
    {
        try {
            DB::beginTransaction();

            $companyRegistration = CompanyRegistration::where(
                [
                    ['confirmation_hash', hash('sha256', $request->input('confirmation_hash'))],
//                    ['confirmation_hash', $request->input('confirmation_hash')],
                    ['confirmed', false],
                ]
            )
                ->first();

            if (!$companyRegistration) {
                return $this->makeErrorResponse(
                    trans('Registration request not found or email confirmation error.'),
                    Response::HTTP_NOT_FOUND
                );
            }

            $companyRegistration->confirmed = true;
            $companyRegistration->confirmed_send_at = now();
            $companyRegistration->save();

            \Illuminate\Support\Facades\Notification::route('mail', config('app.info_email'))
                ->notify(new ConfirmRegistration($companyRegistration));

            $saasAdmins = Admin::get();

            if ($saasAdmins->count()) {
                Notification::send($saasAdmins, new NewCompanyRegistration());
            }


            $company = new Company($companyRegistration->toArray());
            $company->active = true;
            $company->registration_at = $companyRegistration->created_at;
            $company->save();

            $company->createSuperAdmin($companyRegistration, $company->id);
            $company->createSubscription(config('pricing.plans.trial.slug'));

            $company->billingInfo()->create([]);
            $company->insuranceInfo()->create([]);
            $company->notificationSettings()->create([]);

            $companyRegistration->notify(new CompanyRegistrationRequestApprove());
            $companyRegistration->delete();

            $companyService->makeCompanySettingModel($company);
            DB::commit();

            return $this->makeSuccessResponse();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error($exception);

            return $this->makeErrorResponse(
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param CompanyRegistration $companyRegistration
     * @param CompanyService $companyService
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function approve(CompanyRegistration $companyRegistration, CompanyService $companyService): JsonResponse
    {
        $this->authorize(CompanyRegistrationApprove::KEY);

        try {
            DB::beginTransaction();

            $company = new Company($companyRegistration->toArray());
            $company->active = true;
            $company->registration_at = $companyRegistration->created_at;
            $company->save();

            $company->createSuperAdmin($companyRegistration, $company->id);
            $company->createSubscription(config('pricing.plans.trial.slug'));

            $company->billingInfo()->create([]);
            $company->insuranceInfo()->create([]);
            $company->notificationSettings()->create([]);

            $companyRegistration->notify(new CompanyRegistrationRequestApprove());
            $companyRegistration->delete();

            $companyService->makeCompanySettingModel($company);

            DB::commit();

            return $this->makeSuccessResponse();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error($exception);

            return $this->makeErrorResponse(
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param CompanyRegistration $companyRegistration
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function decline(CompanyRegistration $companyRegistration): JsonResponse
    {
        $this->authorize(CompanyRegistrationDecline::KEY);

        try {
            DB::beginTransaction();

            $companyRegistration->notify(new CompanyRegistrationRequestDecline());
            $companyRegistration->delete();

            DB::commit();

            return $this->makeSuccessResponse();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error($exception);

            return $this->makeErrorResponse(
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @param CompanyRegistrationListRequest $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(CompanyRegistrationListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(CompanyRegistrationList::KEY);

        $admins = CompanyRegistration::query()
            ->filter($request->validated())
            ->latest('id')
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return CompanyRegistrationPaginatedResource::collection($admins);
    }

    /**
     * @param CompanyRegistration $companyRegistration
     * @return CompanyRegistrationResource
     * @throws AuthorizationException
     */
    public function show(CompanyRegistration $companyRegistration): CompanyRegistrationResource
    {
        $this->authorize(CompanyRegistrationShow::KEY);

        return CompanyRegistrationResource::make($companyRegistration);
    }
}

/**
 * @see CompanyRegistrationController::registrationRequest()
 *
 * @OA\Post(
 *     path="/v1/saas/company-registration/registration-request",
 *     tags={"V1 Saas Company Registration"},
 *     summary="Create registration request",
 *     operationId="Create registration request",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(
 *         name="ga_id",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="usdot",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="first_name",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="last_name",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="phone",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="password",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="password_confirmation",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Response(response=200, description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/ResponseToken")
 *     ),
 * )
 */

/**
 * @see CompanyRegistrationController::confirmRegistrationEmail()
 *
 * @OA\Post(
 *     path="/v1/saas/company-registration/confirm-registration-email",
 *     tags={"V1 Saas Company Registration"},
 *     summary="Approve registration request email",
 *     operationId="Approve registration request email",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(
 *         name="confirmation_hash",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 * )
 */

/**
 * @see CompanyRegistrationController::decline()
 *
 * @OA\Put(
 *     path="/v1/saas/company-registration/{companyRegistrationId}/decline",
 *     tags={"V1 Saas Company Registration"},
 *     summary="Decline Company Registration Request",
 *     operationId="Decline Company Registration Request",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 * )
 */

/**
 * @see CompanyRegistrationController::approve()
 *
 * @OA\Put(
 *     path="/v1/saas/company-registration/{companyRegistrationId}/approve",
 *     tags={"V1 Saas Company Registration"},
 *     summary="Approve Company Registration Request",
 *     operationId="Approve Company Registration Request",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 * )
 */

/**
 * @see CompanyRegistrationController::show()
 *
 * @OA\Get(
 *     path="/v1/saas/company-registration/{companyRegistrationId}",
 *     tags={"V1 Saas Company Registration"},
 *     summary="Show Company Registration Request",
 *     operationId="Show Company Registration Request",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/CompanyRegistrationResource")
 *     ),
 * )
 */

/**
 * @see CompanyRegistrationController::index()
 *
 * @OA\Get(
 *     path="/v1/saas/company-registration",
 *     tags={"V1 Saas Company Registration"},
 *     summary="Company Registration Request List",
 *     operationId="Company Registration Request List",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
 *          @OA\Schema(type="integer", default="1")
 *     ),
 *     @OA\Parameter(name="per_page", in="query", description="Records per page", required=false,
 *          @OA\Schema(type="integer", default="10")
 *     ),
 *     @OA\Parameter(name="order", in="query", description="Field for sort", required=false,
 *          @OA\Schema(type="string", default="status", enum={"id","created_at"})
 *     ),
 *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
 *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/CompanyRegistrationPaginatedResource")
 *     ),
 * )
 */
