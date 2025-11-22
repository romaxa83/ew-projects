<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\ApiController;
use App\Models\Orders\Order;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lang;

class TestPushController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/api/test/send-push",
     *     tags={"Test"},
     *     summary="Send push message to user",
     *     operationId="Send push message",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Push type",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={
     *                  "dispatcher_need_review_once",
     *                  "dispatcher_pickup_24_once",
     *                  "dispatcher_pickup_1_once",
     *                  "dispatcher_delivery_24_once",
     *                  "dispatcher_delivery_1_once",
     *                  "driver_new_order_once",
     *                  "driver_pickup_24_once",
     *                  "driver_pickup_1_once",
     *                  "driver_delivery_24_once",
     *                  "driver_delivery_1_once",
     *              }
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="message",
     *          in="query",
     *          description="Push message",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="user_id",
     *          in="query",
     *          description="User id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_id",
     *          in="query",
     *          description="Order id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     * )
     *
     */
    public function send(Request $request)
    {
        $request->validate([
            'type' => ['required_without:message', Rule::in([
                "dispatcher_need_review_once",
                "dispatcher_pickup_24_once",
                "dispatcher_pickup_1_once",
                "dispatcher_delivery_24_once",
                "dispatcher_delivery_1_once",
                "driver_new_order_once",
                "driver_pickup_24_once",
                "driver_pickup_1_once",
                "driver_delivery_24_once",
                "driver_delivery_1_once",
            ])],
            'message' => ['required_without:type', 'string', 'min:3'],
            'user_id' => ['required', 'integer', 'exists:App\Models\Users\User,id'],
            'order_id' => ['required', 'integer', 'exists:App\Models\Orders\Order,id'],
        ]);

        $user = User::find($request->user_id);
        $order = Order::find($request->order_id);

        PushNotificationTask::create([
            'type' => $request->type,
            'order_id' => $request->order_id,
            'user_id' => $request->user_id,
            'when' => now()->timestamp,
            'message' =>
                $request->type
                    ? Lang::get('push.' . $request->type, ['load_id' => $order->load_id], $user->language ?? 'en')
                    : $request->message,
            'is_hourly' => false,
        ]);

        return $this->makeSuccessResponse(null, 200);
    }
}
