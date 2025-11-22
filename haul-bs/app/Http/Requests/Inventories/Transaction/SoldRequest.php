<?php

namespace App\Http\Requests\Inventories\Transaction;

use App\Dto\Inventories\SoldDto;
use App\Enums\Inventories\Transaction\DescribeType;
use App\Enums\Inventories\Transaction\PaymentMethod;
use App\Foundations\Enums\EnumHelper;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Inventory;
use App\Repositories\Inventories\InventoryRepository;
use App\Rules\Inventories\QuantityRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(schema="SoldRequest", type="object", allOf={
 *     @OA\Schema(
 *         required={"quantity", "price", "date", "describe"},
 *         @OA\Property(property="quantity", type="number", description="Quantity"),
 *         @OA\Property(property="price", type="number", description="Price"),
 *         @OA\Property(property="invoice_number", type="string", description="Invoice number"),
 *         @OA\Property(property="date", type="string", description="Date, format m/d/Y"),
 *         @OA\Property(property="describe", type="string", description="Describe", enum={"sold","broke","defect"}),
 *         @OA\Property(property="discount", type="number", description="Discount"),
 *         @OA\Property(property="tax", type="number", description="Tax"),
 *         @OA\Property(property="payment_date", type="string", description="Payment date, format m/d/Y"),
 *         @OA\Property(property="payment_method", type="string", description="Payment method", enum={"cash", "check", "money_order", "quick_pay", "paypal", "cashapp", "venmo", "zelle", "credit_card", "card", "wire_transfer"}),
 *         @OA\Property(property="first_name", type="string", description="First name"),
 *         @OA\Property(property="last_name", type="string", description="Last name"),
 *         @OA\Property(property="company_name", type="string", description="Company name"),
 *         @OA\Property(property="phone", type="string", description="Phone"),
 *         @OA\Property(property="email", type="string", description="Email"),
 *     )}
 * )
 */

class SoldRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        /** @var Inventory $inventory */
        $inventory = $this->getModel();

        return [
            'quantity' => ['required', 'numeric', new QuantityRule($inventory->unit_id), 'max:' . $inventory->quantity],
            'date' => ['required', 'string', 'date_format:m/d/Y'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'describe' => ['required', 'string', EnumHelper::ruleIn(DescribeType::class)],
            'discount' => ['nullable', 'numeric', 'min:0.01'],
            'tax' => ['nullable', 'numeric', 'min:0.01'],
            'invoice_number' => ['required_if:describe,sold', 'string', 'max:15'],
            'payment_date' => ['required_if:describe,sold', 'string', 'date_format:m/d/Y'],
            'payment_method' => ['required_if:describe,sold', 'string', EnumHelper::ruleIn(PaymentMethod::class)],
            'first_name' => ['nullable', 'string', Rule::requiredIf(function () {
                if ($this->request->get('describe') !== DescribeType::Sold->value) {
                    return false;
                }

                if (!empty($this->request->get('company_name'))) {
                    return false;
                }

                return true;
            })],
            'last_name' => ['nullable', 'string', Rule::requiredIf(function () {
                if ($this->request->get('describe') !== DescribeType::Sold->value) {
                    return false;
                }

                if (!empty($this->request->get('company_name'))) {
                    return false;
                }

                return true;
            })],
            'company_name' => ['nullable', 'string', Rule::requiredIf(function () {
                if ($this->request->get('describe') !== DescribeType::Sold->value) {
                    return false;
                }

                if (!empty($this->request->get('first_name')) && !empty($this->request->get('last_name'))) {
                    return false;
                }

                return true;
            })],
            'phone' => ['required_if:describe,sold', 'string', new PhoneRule()],
            'email' => ['required_if:describe,sold', 'string', 'email'],
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

    public function getDto(): SoldDto
    {
        return SoldDto::byArgs($this->validated());
    }
}
