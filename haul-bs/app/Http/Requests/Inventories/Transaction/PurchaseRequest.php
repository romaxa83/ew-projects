<?php

namespace App\Http\Requests\Inventories\Transaction;

use App\Dto\Inventories\PurchaseDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Inventory;
use App\Repositories\Inventories\InventoryRepository;
use App\Rules\Inventories\QuantityRule;

/**
 * @OA\Schema(schema="PurchaseRequest", type="object", allOf={
 *     @OA\Schema(
 *         required={"quantity", "cost", "date"},
 *         @OA\Property(property="quantity", type="number", description="Quantity"),
 *         @OA\Property(property="cost", type="number", description="Cost"),
 *         @OA\Property(property="invoice_number", type="string", description="Invoice number"),
 *         @OA\Property(property="date", type="string", description="Date, format m/d/Y"),
 *     )}
 * )
 */

class PurchaseRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        /** @var Inventory $inventory */
        $inventory = $this->getModel();

        return [
            'quantity' => ['required', 'numeric', new QuantityRule($inventory->unit_id)],
            'date' => ['required', 'string', 'date_format:m/d/Y'],
            'cost' => ['required', 'numeric', 'min:0.01'],
            'invoice_number' => ['nullable', 'string', 'max:15'],
        ];
    }

    public function getModel(): Inventory
    {
        $id = $this->route('id');

        /** @var $repo InventoryRepository */
        $repo = resolve(InventoryRepository::class);

        /** @var $model Inventory */
        $model = $repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        return $model;
    }

    public function getDto(): PurchaseDto
    {
        return PurchaseDto::byArgs($this->validated());
    }
}
