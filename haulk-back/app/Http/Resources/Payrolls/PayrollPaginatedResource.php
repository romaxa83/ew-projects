<?php

namespace App\Http\Resources\Payrolls;

use App\Http\Resources\Users\UserMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPaginatedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(schema="PayrollPaginatedResource",
     *     @OA\Property(property="data", type="array",
     *         @OA\Items(allOf={
     *             @OA\Schema(
     *                 @OA\Property(property="id", type="integer",),
     *                 @OA\Property(property="driver", type="object",),
     *                 @OA\Property(property="salary", type="number",),
     *                 @OA\Property(property="is_paid", type="boolean",),
     *                 @OA\Property(property="notes", type="string",),
     *                 @OA\Property(property="created_at", type="integer",),
     *                 @OA\Property(property="paid_at", type="integer",),
     *                 @OA\Property(property="public_token", type="string",),
     *                 @OA\Property(property="start", type="integer", description="Payroll start at", nullable=true),
     *                 @OA\Property(property="end", type="integer", description="Payroll end at", nullable=true),
     *                 @OA\Property(property="send_pdf_at", type="integer", description="Send pdf at", nullable=true),
     *             )
     *         })
     *    ),
     *    @OA\Property(property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     */
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'driver' => UserMiniResource::make($this->driver),
            'salary' => (double) $this->salary,
            'is_paid' => $this->is_paid,
            'notes' => $this->notes,
            'created_at' => $this->created_at->timestamp,
            'paid_at' => $this->paid_at,
            'public_token' => $this->public_token,
            'start' => $this->start->timestamp ?? null,
            'end' => $this->end->timestamp ?? null,
            'start_as_str' => $this->start ? $this->start->format('Y-m-d H:i:s') : null,
            'end_as_str' => $this->end ? $this->end->format('Y-m-d H:i:s') : null,
            'send_pdf_at' => $this->send_pdf_at->timestamp ?? null,
            'start_as_str' => $this->start ? $this->start->format('Y-m-d H:i:s') : null,
            'end_as_str' => $this->end ? $this->end->format('Y-m-d H:i:s') : null,
        ];
    }
}
