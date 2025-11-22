<?php


namespace App\Http\Resources\Orders;


use App\Models\Orders\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentStageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="PaymentStageResourceRaw",
     *    type="object",
     *    allOf={
     *        @OA\Schema(
     *            @OA\Property(property="id", type="integer"),
     *            @OA\Property(property="amount", type="number"),
     *            @OA\Property(property="payment_date", type="integer"),
     *            @OA\Property(property="payer", type="string"),
     *            @OA\Property(property="method_id", type="integer"),
     *            @OA\Property(property="method", type="object", allOf={@OA\Schema(ref="#/components/schemas/PaymentMethodResourceRaw")}),
     *            @OA\Property(property="uship_number", type="string"),
     *            @OA\Property(property="reference_number", type="string"),
     *            @OA\Property(property="notes", type="string"),
     *        )
     *    }
     * )
     *
     * @OA\Schema(
     *    schema="PaymentStageResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(ref="#/components/schemas/PaymentStageResourceRaw")
     *        }
     *    )
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'id' => (int)$this->id,
            'amount' => (double)$this->amount,
            'payment_date' => (int)$this->payment_date,
            'payer' => $this->payer,
            'method_id' => (int)$this->method_id,
            'method' => $this->method_id ? [
                'id' => $this->method_id,
                'title' => Payment::ALL_METHODS[$this->method_id] ?? '',
            ] : null,
            'uship_number' => $this->uship_number,
            'reference_number' => $this->reference_number,
            'notes' => $this->notes,
        ];
    }
}
