<?php

namespace App\Http\Resources\Payrolls;

use App\Http\Resources\Users\UserMiniResource;
use App\Models\Orders\Order;
use App\Models\Payrolls\Payroll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="PayrollResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="id", type="integer",),
     *                @OA\Property(property="driver_id", type="integer",),
     *                @OA\Property(property="driver", type="object", ref="#/components/schemas/UserMini"),
     *                @OA\Property(property="driver_rate", type="number"),
     *                @OA\Property(property="total", type="number",),
     *                @OA\Property(property="subtotal", type="number",),
     *                @OA\Property(property="commission", type="number",),
     *                @OA\Property(property="salary", type="number",),
     *                @OA\Property(property="expenses_before", type="array",
     *                      @OA\Items(
     *                          allOf={
     *                              @OA\Schema (
     *                                  @OA\Property (property="load_id", type="string"),
     *                                  @OA\Property (property="type", type="string"),
     *                                  @OA\Property (property="price", type="number", format="float"),
     *                                  @OA\Property (property="date", type="integer", nullable="true"),
     *                                  @OA\Property (property="note", type="string", nullable="true"),
     *                              )
     *                          }
     *                      )
     *                ),
     *                @OA\Property(property="expenses_after", type="array",
     *                      @OA\Items(
     *                          allOf={
     *                              @OA\Schema (
     *                                  @OA\Property (property="load_id", type="string"),
     *                                  @OA\Property (property="type", type="string"),
     *                                  @OA\Property (property="price", type="number", format="float"),
     *                                  @OA\Property (property="date", type="integer", nullable="true"),
     *                                  @OA\Property (property="note", type="string", nullable="true"),
     *                              )
     *                          }
     *                      )
     *                ),
     *                @OA\Property(property="bonuses", type="array", @OA\Items()),
     *                @OA\Property(property="is_paid", type="boolean",),
     *                @OA\Property(property="created_at", type="integer",),
     *                @OA\Property(property="paid_at", type="integer",),
     *                @OA\Property(property="orders", type="array", @OA\Items(
     *                      allOf={
     *                          @OA\Schema (
     *                              @OA\Property (property="load_id", type="string"),
     *                              @OA\Property (property="id", type="string"),
     *                          )
     *                      }
     *                )),
     *                @OA\Property(property="notes", type="string",),
     *                @OA\Property(property="start", type="integer",),
     *                @OA\Property(property="end", type="integer",),
     *                @OA\Property(property="public_token", type="string",),
     *            )
     *        }
     *    ),
     * )
     *
     */
    public function toArray($request): array
    {
        /**@var Payroll $this */
        $orders = $this->orders->count() ? $this->orders :
            Order::whereIn(
                Order::TABLE_NAME . '.id',
                array_map(
                    static fn(array $item) => $item['id'],
                    $request->input('orders')
                )
            )
                ->get();

        return [
            'id' => (int)$this->id,
            'driver_id' => (int)$this->driver_id,
            'driver' => UserMiniResource::make($this->driver),
            'driver_rate' => (double)$this->driver_rate,
            'total' => (double)$this->total,
            'subtotal' => (double)$this->subtotal,
            'commission' => (double)$this->commission,
            'salary' => (double)$this->salary,
            'expenses_before' => $this->expenses_before ?? [],
            'expenses_after' => $this->expenses_after ?? [],
            'bonuses' => $this->bonuses ?? [],
            'is_paid' => $this->is_paid,
            'created_at' => isset($this->created_at) ? $this->created_at->timestamp : null,
            'paid_at' => $this->paid_at,
            'notes' => $this->notes,
            'start' => $this->start->timestamp ?? null,
            'end' => $this->end->timestamp ?? null,
            'public_token' => $this->public_token,
            'orders' => $orders->map(
                function ($el) {
                    return [
                        'load_id' => $el->load_id,
                        'id' => $el->id,
                    ];
                }
            )->toArray()
        ];
    }
}
