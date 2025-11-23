<?php

namespace Database\Factories\Orders\Payroll;

use App\Enums\Orders\PayrollPaymentType;
use App\Models\Order;
use App\Models\Order\Payroll\Payroll;
use Database\Factories\BaseFactory;

class PayrollFactory extends BaseFactory
{
    protected $model = Payroll::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'hours' => 5,
            'paid_form_bol' => '{"cash": 33.9, "zelle": "", "credit_card": 49.5, "credit_card_fee": 10, "credit_card_clean": ""}',
            'start_at' => null,
            'end_at' => null,
            'is_processed' => false,
            'processed_employee_id' => null,
            'processed_at' => null,
        ];
    }
}