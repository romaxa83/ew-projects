<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\CreatePaymentRequest;
use App\Models\Order\Payment;
use App\Models\PaymentAccount;
use DB;
use Illuminate\Http\{JsonResponse, Request};
use Auth;

/**
 * Order Payments
 */
class PaymentController extends Controller
{
    /**
     * Get all payments for order.
     * @param  Payment  $payment
     * @param  Request  $request
     * @return JsonResponse
     */
    public function records(Payment $payment, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $records = $payment
            ->where('order_id', $validated['order_id'])
            ->with('account:id,title')
            ->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records,
                'accounts' => PaymentAccount::records()->where('division_id', $request->session()->get('division.id'))->get()
            ]);
    }

    /**
     * Add new payment record.
     * @param  CreatePaymentRequest  $request
     * @param  Payment  $payment
     * @return JsonResponse
     */
    public function create(CreatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $validated = $request->validated();

        $record = clone $payment;
        $record->user_id = Auth::id();
        $record->amount = $validated['amount'];
        $record->order_id = $validated['order_id'];
        $record->payment_account_id = $validated['account_id'];
        $record->description = $validated['description'];
        $record->in_total_sum = $validated['in_total'];
        $record->save();

        $records = $payment
            ->where('order_id', $validated['order_id'])
            ->with('account:id,title')
            ->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records,
                'accounts' => PaymentAccount::records()->get()
            ]);
    }

    /**
     * Toggle value $in_total_sum for payment transaction.
     * @param  Request  $request
     * @param  Payment  $payment
     * @return JsonResponse
     *
     * test @see \Tests\Feature\Orders\Payments\ToggleInTotalTest
     */
    public function toggleInTotal(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'payment_id' => 'required|integer|exists:orders_payments,id',
        ]);

        $model = Payment::where('id', $validated['payment_id'])->first();
        $model->in_total_sum = !$model->in_total_sum;
        $model->save();

        $records = $payment
            ->where('order_id', $validated['order_id'])
            ->with('account:id,title')
            ->get();


        return $this->responseDataJson([
            'success' => true,
            'records' => $records,
        ]);
    }
}
