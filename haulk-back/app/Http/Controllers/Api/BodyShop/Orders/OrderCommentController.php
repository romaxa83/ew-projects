<?php

namespace App\Http\Controllers\Api\BodyShop\Orders;

use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Orders\Comments\OrderCommentRequest;
use App\Http\Resources\BodyShop\Orders\Comments\OrderCommentResource;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\OrderComment;
use App\Services\BodyShop\Orders\OrderService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;
use Throwable;

class OrderCommentController extends ApiController
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        parent::__construct();

        $this->service = $service->setUser(authUser());
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/body-shop/orders/{orderId}/comments",
     *     tags={"Order comments Body Shop"},
     *     summary="Get comments paginated list",
     *     operationId="Get comments data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderCommentBSListResource")
     *     ),
     * )
     */
    public function index(Request $request, Order $order)
    {
        $this->authorize('orders-bs');

        return OrderCommentResource::collection(
            $order->comments()
                ->orderBy('id')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Order $order
     * @param OrderCommentRequest $request
     * @return JsonResponse|OrderCommentResource
     *
     * @OA\Post(
     *     path="/api/body-shop/orders/{orderId}/comments",
     *     tags={"Order comments Body Shop"},
     *     summary="Create comment",
     *     operationId="Create comment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="comment", in="query", description="Order comment", required=true,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderCommentBSResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Order $order, OrderCommentRequest $request)
    {
        $this->authorize('orders-bs add-comment');

        try {
            return new OrderCommentResource($this->service->addComment($order, $request->validated()));
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Order $order
     * @param OrderComment $comment
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/body-shop/orders/{orderId}/comments/{commentId}",
     *     tags={"Order comments Body Shop"},
     *     summary="Delete comment",
     *     operationId="Delete comment",
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
     *
     * @throws Exception
     */
    public function destroy(Request $request, Order $order, OrderComment $comment): JsonResponse
    {
        $this->authorize('orders-bs delete-comment');

        try {
            $this->service->deleteComment($order, $comment);

            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }
}
