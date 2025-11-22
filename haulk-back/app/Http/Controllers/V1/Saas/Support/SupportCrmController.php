<?php


namespace App\Http\Controllers\V1\Saas\Support;


use App\Http\Requests\Saas\Support\Crm\StoreRequest;
use App\Http\Requests\Saas\Support\Crm\IndexRequest;
use App\Http\Resources\Saas\Support\Crm\SupportResource;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Users\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class SupportCrmController extends SupportController
{

    protected bool $isAdminPanel = false;

    /**
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/v1/saas/support/crm/",
     *     tags={"Saas Support"},
     *     summary="Get support request list (CRM)",
     *     operationId="Get support request list (CRM)",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Records per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field to sort by", required=false,
     *          @OA\Schema(type="string", default="created_at", enum ={"created_at, updated_at"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Sort type", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"desc, asc"})
     *     ),
     *     @OA\Parameter(name="only_my", in="query", description="Show only user request", required=false,
     *          @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Status filter", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="Date filter", required=false,
     *          @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Date filter", required=false,
     *          @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SupportRequestsCrmPaginatedResource")
     *     ),
     * )
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        return SupportResource::collection(
            $this->supportService->getSupportRequestList($request->validated())
        );
    }

    /**
     * @param SupportRequest $supportRequest
     * @return SupportResource
     * @throws AuthorizationException
     *
     * @OA\Get (
     *     path="/v1/saas/support/crm/{id}",
     *     tags={"Saas Support"},
     *     summary="Get support request",
     *     operationId="Get support request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=200, description="Success response", @OA\JsonContent(ref="#/components/schemas/SupportRequestCrmResource")),
     * )
     */
    public function show(SupportRequest $supportRequest): SupportResource
    {
        $this->authorize('read', $supportRequest) ;

        return SupportResource::make($supportRequest);
    }

    /**
     * @param StoreRequest $request
     * @return SupportResource|JsonResponse
     * @throws Throwable
     *
     * @OA\Post(
     *     path="/v1/saas/support/crm",
     *     tags={"Saas Support"},
     *     summary="Create new support request",
     *     operationId="Create new support request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(
     *         parameter="Authorization",
     *         name="X-Authorization",
     *         in="header",
     *         required=false,
     *         @OA\Schema(
     *            type="string"
     *         )
     *     ),
     *     @OA\Parameter(name="user_name", in="query", description="User name", required=true,
     *          @OA\Schema(type="string", minLength=2, maxLength=255)
     *     ),
     *     @OA\Parameter(name="user_email", in="query", description="User email", required=true,
     *          @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Parameter(name="user_phone", in="query", description="User phone", required=true,
     *          @OA\Schema(type="string", pattern="/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/")
     *     ),
     *     @OA\Parameter(name="subject", in="query", description="Support request subject", required=true,
     *          @OA\Schema(type="string", minLength=2, maxLength=255)
     *     ),
     *     @OA\Parameter(name="message", in="query", description="Support request message", required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="attachments", in="query", description="attachment files", required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string", format="binary"))
     *     ),
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/SupportRequestCrmResource")),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(StoreRequest $request)
    {
        try {
            $user = $request->user(User::GUARD);

            $supportRequest = $this->supportService->createSupportRequest($request->user(User::GUARD), $request->validated());

            if ($user) {
                return SupportResource::make($supportRequest);
            }

            return $this->makeSuccessResponse(null, Response::HTTP_CREATED);
        } catch (Exception $e){
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SupportRequest $supportRequest
     * @return JsonResponse|SupportResource
     * @throws AuthorizationException
     * @OA\Put (
     *     path="/v1/saas/support/crm/{id}/close",
     *     tags={"Saas Support"},
     *     summary="Close support request",
     *     operationId="Close support request",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=200, description="Success response", @OA\Schema (ref="#/components/schemas/SupportRequestResource")),
     *
     * )
     */
    public function close(SupportRequest $supportRequest)
    {
        $this->authorize('crmClose', $supportRequest);

        if ($this->supportService->changeStatus($supportRequest, SupportRequest::STATUS_CLOSED)) {
            $supportRequest->refresh();
            return SupportResource::make($supportRequest);
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
