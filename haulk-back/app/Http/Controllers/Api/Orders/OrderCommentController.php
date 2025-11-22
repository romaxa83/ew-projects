<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Orders\OrderCommentRequest;
use App\Http\Resources\Orders\OrderCommentListResource;
use App\Http\Resources\Orders\OrderCommentResource;
use App\Models\Orders\Order;
use App\Models\Orders\OrderComment;
use App\Services\Events\EventService;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;
use Throwable;

class OrderCommentController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection|JsonResponse
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/orders/{orderId}/comments",
     *     tags={"Order comments"},
     *     summary="Get comments paginated list",
     *     operationId="Get comments data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="comment",
     *          in="query",
     *          description="Order comment",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderCommentListResource")
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('orders');

        //todo добавить норм валидацию в реквесте

        if (
            Order::query()
                ->where(
                    [
                        Order::TABLE_NAME . '.id' => $request->order_id,
                        'carrier_id' => $request->user()->getCompany()->id
                    ]
                )
                ->doesntExist()
        ) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        return OrderCommentListResource::collection(
            OrderComment::query()
                ->where('order_id', $request->order_id)
                ->with('user.roles')
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
     *     path="/api/orders/{orderId}/comments",
     *     tags={"Order comments"},
     *     summary="Create comment",
     *     operationId="Create comment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="comment",
     *          in="query",
     *          description="Order comment",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderCommentResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Order $order, OrderCommentRequest $request)
    {
        $this->authorize('orders add-comment');

        try {
            DB::beginTransaction();

            $event = EventService::comment($order)
                ->user($request->user());

            $comment = new OrderComment($request->validated());
            $comment->user_id = $request->user()->id;
            $comment->role_id = $request->user()->roles->first()->id;
            $comment->timezone = $request->header('TimezoneId', null);

            $order->comments()->save($comment);

            $event->create()
                ->broadcast();

            DB::commit();

            return new OrderCommentResource($comment);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Order $order
     * @param OrderComment $comment
     * @return OrderCommentResource
     *
     * @OA\Get(
     *     path="/api/orders/{orderId}/comments/{commentId}",
     *     tags={"Order comments"},
     *     summary="Get comment info",
     *     operationId="Get comment data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderCommentResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function show(Order $order, OrderComment $comment): OrderCommentResource
    {
        $this->authorize('orders');

        return new OrderCommentResource($comment);
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
     *     path="/api/orders/{orderId}/comments/{commentId}",
     *     tags={"Order comments"},
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
        $this->authorize('orders delete-comment');

        try {
            $event = EventService::comment($order, $comment)
                ->user($request->user());

            $comment->delete();

            $event->delete();

            return $this->makeSuccessResponse(null, 204);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), 500);
        }
    }
}
