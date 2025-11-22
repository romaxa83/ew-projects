<?php

namespace App\Enums\Inventories\Transaction;

enum PaymentMethod: string {
    case Cash = "cash";
    case Check = "check";
    case MoneyOrder = "money_order";
    case QuickPay = "quick_pay";
    case Paypal = "paypal";
    case CashApp = "cashapp";
    case Venmo = "venmo";
    case Zelle = "zelle";
    case CreditCard = "credit_card";
    case Card = "card";
    case WireTransfer = "wire_transfer";

    public function label(): string
    {
        return match ($this->value){
            static::Cash->value => 'Cash',
            static::Check->value => 'Check',
            static::MoneyOrder->value => 'Money Order',
            static::QuickPay->value => 'Quick Pay',
            static::Paypal->value => 'PayPal',
            static::CashApp->value => 'Cashapp',
            static::Venmo->value => 'Venmo',
            static::Zelle->value => 'Zelle',
            static::CreditCard->value => 'Credit Card',
            static::Card->value => 'Card',
            static::WireTransfer->value => 'Wire transfer',
            default => throw new \Exception('Unexpected match value'),
        };
    }
}


