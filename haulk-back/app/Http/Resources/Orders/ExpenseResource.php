<?php

namespace App\Http\Resources\Orders;

use App\Models\Orders\Expense;
use App\Http\Resources\Files\FileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="ExpenseResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="type_id", type="number", description="Expense type"),
     *                @OA\Property(property="price", type="number", description="Expense price"),
     *                @OA\Property(property="date", type="integer", description="Expense date timestamp"),
     *                @OA\Property(property="receipt", type="object", description="Expense receipt image", allOf={
     *                              @OA\Schema(ref="#/components/schemas/File")
     *                        }),
     *                @OA\Property(property="to", type="string", description="User type to", enum={"broker","customer"}, nullable=true),
     *
     *            )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="ExpenseResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Order expense data",
     *            allOf={
     *            @OA\Schema(
     *                @OA\Property(property="type_id", type="number", description="Expense type"),
     *                @OA\Property(property="price", type="number", description="Expense price"),
     *                @OA\Property(property="date", type="integer", description="Expense date timestamp"),
     *                @OA\Property(property="receipt", type="object", description="Expense receipt image", allOf={
     *                              @OA\Schema(ref="#/components/schemas/File")
     *                        }),
     *                @OA\Property(property="to", type="string", description="User type to", enum={"broker","customer"}, nullable=true),
     *            )
     *        }
     *        ),
     * )
     *
     */
    public function toArray($request): array
    {
        /**@var Expense $this*/
        return [
            'id' => $this->id,
            'type_id' => $this->type_id,
            'price' => (double) $this->price,
            'date' => $this->date ? (int) $this->date : null,
            'receipt' => FileResource::make($this->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)),
            'to' => $this->to
        ];
    }
}
