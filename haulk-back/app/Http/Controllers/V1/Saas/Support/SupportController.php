<?php

namespace App\Http\Controllers\V1\Saas\Support;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Saas\Support\StoreMessageRequest;
use App\Http\Requests\Saas\Support\ShowMessageRequest;
use App\Http\Requests\Saas\Support\IndexMessageRequest;
use App\Http\Resources\Saas\Support\LabelsResource;
use App\Http\Resources\Saas\Support\SourcesResource;
use App\Http\Resources\Saas\Support\SupportMessageResource;
use App\Http\Resources\Saas\Support\StatusesResource;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Saas\Support\SupportRequestMessage;
use App\Services\Saas\Support\SupportService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class SupportController extends ApiController
{

    protected bool $isAdminPanel = false;

    protected SupportService $supportService;

    public function __construct(SupportService $supportService)
    {
        parent::__construct();

        $this->supportService = $supportService->setIsAdminPanel($this->isAdminPanel)
            ->setUser($this->isAdminPanel ? authAdmin() : authUser());
    }

    /**
     * @return StatusesResource
     *
     * @OA\Get(
     *     path="/v1/saas/support/statuses",
     *     tags={"Saas Support"},
     *     summary="Get support request statuses list",
     *     operationId="Get support request statuses list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SupportRequestStatuses")
     *     ),
     * )
     */
    public function getStatusesList(): StatusesResource
    {
        return StatusesResource::make(['statuses' => SupportRequest::STATUSES_DESCRIPTION]);
    }

    /**
     * @return SourcesResource
     *
     * @OA\Get(
     *     path="/v1/saas/support/sources",
     *     tags={"Saas Support"},
     *     summary="Get support request sources list",
     *     operationId="Get support request sources list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SupportRequestSources")
     *     ),
     * )
     */
    public function getSourcesList(): SourcesResource
    {
        return SourcesResource::make(['sources' => SupportRequest::SOURCES_DESCRIPTION]);
    }

    /**
     * @return LabelsResource
     *
     * @OA\Get(
     *     path="/v1/saas/support/labels",
     *     tags={"Saas Support"},
     *     summary="Get support request labels list",
     *     operationId="Get support request labels list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SupportRequestLabels")
     *     ),
     * )
     */
    public function getLabelsList(): LabelsResource
    {
        return LabelsResource::make(['labels' => SupportRequest::LABELS_DESCRIPTION]);
    }

    /**
     * @param IndexMessageRequest $request
     * @param SupportRequest $supportRequest
     * @return AnonymousResourceCollection
     *
     * @OA\Get (
     *     path="/v1/saas/support/{from}/{id}/messages",
     *     tags={"Saas Support"},
     *     summary="Get support request's messages",
     *     operationId="Get support request's messages",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Parameter (name="from", in="path", required=true, description="Where from request", @OA\Schema (type="string", enum={"crm", "back-office"})),
     *     @OA\Parameter (name="older_than", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter (name="newer_than", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=200, description="Success response", @OA\JsonContent(ref="#/components/schemas/SupportMessagePaginatedResource")),
     * )
     */
    public function indexMessages(IndexMessageRequest $request, SupportRequest $supportRequest): AnonymousResourceCollection
    {
        $messagesList = $this->supportService->getMessagesList(
            $supportRequest,
            $request->user(),
            $request->input('older_than'),
            $request->input('newer_than'),
            $request->input('per_page')
        );

        return SupportMessageResource::collection($messagesList->getMessages())
            ->additional(
                [
                    'meta' => [
                        'has_older' => $messagesList->hasOlder()
                    ]
                ]
            );
    }

    /**
     * @param StoreMessageRequest $request
     * @param SupportRequest $supportRequest
     * @return SupportMessageResource|JsonResponse
     * @throws Throwable
     *
     * @OA\Post (
     *     path="/v1/saas/support/{from}/{id}/message",
     *     tags={"Saas Support"},
     *     summary="Add new message",
     *     operationId="Add new message",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Parameter (name="from", in="path", required=true, description="Where from request", @OA\Schema (type="string", enum={"crm", "back-office"})),
     *     @OA\Parameter (name="message", in="query", required=true, description="New message", @OA\Schema (type="string")),
     *     @OA\Parameter(name="attachments", in="query", description="attachment files", required=false,
     *          @OA\Schema(type="array", @OA\Items(type="string", format="binary"))
     *     ),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=200, description="Success response", @OA\JsonContent(ref="#/components/schemas/SupportRequestMessageResource")),
     * )
     */
    public function storeMessage(StoreMessageRequest $request, SupportRequest $supportRequest)
    {
        try {
            return SupportMessageResource::make(
                $this->supportService->addMessageInSupportRequest(
                    $supportRequest,
                    $request->validated()
                )
            );
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param ShowMessageRequest $request
     * @param SupportRequest $supportRequest
     * @param SupportRequestMessage $supportRequestMessage
     * @return SupportMessageResource
     *
     * @OA\Get (
     *     path="/v1/saas/support/{from}/{id}/message/{message-id}",
     *     tags={"Saas Support"},
     *     summary="Get support request's message",
     *     operationId="Get support request's message",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter (name="id", in="path", required=true, description="Support request ID", @OA\Schema (type="integer")),
     *     @OA\Parameter (name="from", in="path", required=true, description="Where from request", @OA\Schema (type="string", enum={"crm", "back-office"})),
     *     @OA\Parameter (name="message-id", in="path", required=true, description="Support request message ID", @OA\Schema (type="integer")),
     *     @OA\Response (response=403, description="Forbidden"),
     *     @OA\Response (response=404, description="Request not found"),
     *     @OA\Response (response=200, description="Success response", @OA\JsonContent(ref="#/components/schemas/SupportRequestMessageResource")),
     * )
     */
    public function showMessage(ShowMessageRequest $request, SupportRequest $supportRequest, SupportRequestMessage $supportRequestMessage): SupportMessageResource
    {
        $this->supportService->readingMessages($supportRequest, $supportRequestMessage, $request->user());
        return SupportMessageResource::make($supportRequestMessage);
    }
}
