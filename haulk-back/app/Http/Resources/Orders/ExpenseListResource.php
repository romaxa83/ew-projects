<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Files\FileResource;
use App\Models\Orders\Expense;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseListResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @OA\Schema(
     *    schema="ExpenseListResource",
     *    @OA\Property(
     *        property="data",
     *        description="Orders expenses list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/ExpenseResourceRaw")
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        /**@var Expense $this*/
        return [
            'id' => $this->id,
            'type_id' => $this->type_id,
            'price' => (double)$this->price,
            'date' => $this->date ? (int)$this->date : null,
            'receipt' => $this->relationLoaded('media') ?
                FileResource::make($this->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)) : null,
            'to' => $this->to,
        ];
    }
}
