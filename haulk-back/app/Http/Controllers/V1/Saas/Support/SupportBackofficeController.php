<?php


namespace App\Http\Controllers\V1\Saas\Support;

use App\Http\Requests\Saas\Support\Backoffice\ChangeManagerRequest;
use App\Http\Requests\Saas\Support\Backoffice\CloseRequest;
use App\Http\Requests\Saas\Support\Backoffice\IndexRequest;
use App\Http\Requests\Saas\Support\Backoffice\SetLabelRequest;
use App\Http\Resources\Saas\Support\Backoffice\SupportResource;
use App\Models\Saas\Support\SupportRequest;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SupportBackofficeController extends SupportController
{

    protected bool $isAdminPanel = true;

    /**
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/v1/saas/support/back-office/",
     *     tags={"Saas Support"},
     *     summary="Get support request list (Back-office)",
     *     operationId="Get support request list (Back-office)",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter (name="per_page", in="query", description="Records per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter (name="order_by", in="query", description="Field to sort by", required=false,
     *          @OA\Schema(type="string", default="created_at", enum ={"created_at, updated_at"})
     *     ),
     *     @OA\Parameter (name="order_type", in="query", description="Sort type", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"desc, asc"})
     *     ),
     *     @OA\Parameter (name="admin", in="query", description="admin filter", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter (name="status", in="query", description="Status filter", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter (name="label", in="query", description="Label filter", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter (name="date_from", in="query", description="Date filter", required=false,
     *          @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter (name="date_to", in="query", description="Date filter", required=false,
     *          @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SupportRequestsBackOfficePaginatedResource")
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
     * @OA\Get (
     *     path="/v1/saas/support/back-office/{id}",
     *     tags={"Saas Support"},
     *     summary="Get support request",
     *     operationId="Get support request",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=200, description="Success response", @OA\JsonContent(ref="#/components/schemas/SupportRequestBackofficeResource")),
     * )
     */
    public function show(SupportRequest $supportRequest): SupportResource
    {
        $this->authorize('read', $supportRequest);

        $this->supportService->addViewer($supportRequest);

        return SupportResource::make($supportRequest);
    }

    /**
     * @param Request $request
     * @param SupportRequest $supportRequest
     * @return SupportResource|JsonResponse
     * @throws AuthorizationException
     * @OA\Put (
     *     path="/v1/saas/support/back-office/{id}/take",
     *     tags={"Saas Support"},
     *     summary="Take request (from Back-office)",
     *     operationId="Take request (from Back-office)",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=200, description="Success response", @OA\Schema (ref="#/components/schemas/SupportRequestBackofficeResource")),
     * )
     */
    public function take(Request $request, SupportRequest $supportRequest)
    {
//        $this->authorize('take', $supportRequest);

        if ($this->supportService->takeSupportRequest($supportRequest, $request->user())) {
            $supportRequest->refresh();
            return SupportResource::make($supportRequest);
        }
        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param CloseRequest $request
     * @param SupportRequest $supportRequest
     * @return JsonResponse|SupportResource
     * @throws AuthorizationException
     * @OA\Put (
     *     path="/v1/saas/support/back-office/{id}/close",
     *     tags={"Saas Support"},
     *     summary="Close request",
     *     operationId="Close request",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Support request ID",
     *          @OA\Schema (
     *              type="integer"
     *          )
     *     ),
     *     @OA\Parameter (
     *          name="closing_reason",
     *          in="query",
     *          required=true,
     *          description="Reason of closing",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=200, description="Success response", @OA\Schema (ref="#/components/schemas/SupportRequestBackofficeResource")),
     * )
     */
    public function close(CloseRequest $request, SupportRequest $supportRequest)
    {
        if ($this->supportService->closeSupportRequest($supportRequest, $request->closingReason())) {
            $supportRequest->refresh();
            return SupportResource::make($supportRequest);
        }

        return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param SetLabelRequest $request
     * @param SupportRequest $supportRequest
     * @return JsonResponse|SupportResource
     * @OA\Put (
     *     path="/v1/saas/support/back-office/{id}/set-label",
     *     tags={"Saas Support"},
     *     summary="Set label at support request",
     *     operationId="Set label at support request",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Parameter (name="label", in="query", required=true, description="Label ID", @OA\Schema (type="integer")),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=200, description="Success response", @OA\Schema (ref="#/components/schemas/SupportRequestBackofficeResource")),
     * )
     */
    public function setLabel(SetLabelRequest $request, SupportRequest $supportRequest)
    {
        try {
            return SupportResource::make(
                $this->supportService->setLabel($request->input('label'), $supportRequest)
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param ChangeManagerRequest $request
     * @param SupportRequest $supportRequest
     * @return JsonResponse|SupportResource
     * @OA\Put (
     *     path="/v1/saas/support/back-office/{id}/change-manager",
     *     tags={"Saas Support"},
     *     summary="Set other manager at support request",
     *     operationId="Set other manager at support request",
     *     deprecated=false,
     *     @OA\Parameter (ref="#/components/parameters/Content-type"),
     *     @OA\Parameter (ref="#/components/parameters/Accept"),
     *     @OA\Parameter (ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter (ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Parameter (name="manager_id", in="query", description="New manager ID", @OA\Schema (type="integer", nullable=true)),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=200, description="Success response", @OA\Schema (ref="#/components/schemas/SupportRequestBackofficeResource")),
     * )
     */
    public function changeManager(ChangeManagerRequest $request, SupportRequest $supportRequest)
    {
        try {
            return SupportResource::make(
                $this->supportService->changeManager($request->validated(), $supportRequest)
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
