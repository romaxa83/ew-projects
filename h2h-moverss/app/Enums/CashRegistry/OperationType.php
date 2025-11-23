<?php

namespace App\Enums\CashRegistry;

use App\Enums\Base\InvokableCases;
use App\Enums\Base\RuleIn;

/**
 * @method static CASH_COLLECTION()
 * @method static CASH_DISBURSEMENT()
 * @method static PAYROLL_CASH_COLLECTED()
 * @method static PAYROLL_CASH_PAID()
 * @method static CASH_TRANSFER()
 */

enum OperationType: string {

    use InvokableCases;
    use RuleIn;

    case CASH_COLLECTION  = "cash_collection";
    case CASH_DISBURSEMENT  = "cash_disbursement";
    case PAYROLL_CASH_COLLECTED  = "payroll_cash_collected";
    case PAYROLL_CASH_PAID  = "payroll_cash_paid";
    case CASH_TRANSFER  = "cash_transfer";

    public function isCashCollection(): bool
    {
        return $this->value === self::CASH_COLLECTION->value;
    }

    public function isCashDisbursement(): bool
    {
        return $this->value === self::CASH_DISBURSEMENT->value;
    }
    public function isPayrollCashCollected(): bool
    {
        return $this->value === self::PAYROLL_CASH_COLLECTED->value;
    }
    public function isPayrollCashPaid(): bool
    {
        return $this->value === self::PAYROLL_CASH_PAID->value;
    }

    public function isCashTransfer(): bool
    {
        return $this->value === self::CASH_TRANSFER->value;
    }

    public static function forForm(): array
    {
        return [
            self::CASH_COLLECTION->value => self::CASH_COLLECTION->label(),
            self::CASH_DISBURSEMENT->value => self::CASH_DISBURSEMENT->label(),
            self::CASH_TRANSFER->value => self::CASH_TRANSFER->label(),
        ];
    }

    public static function forFilter(): array
    {
        return [
            self::CASH_COLLECTION->value => self::CASH_COLLECTION->label(),
            self::CASH_DISBURSEMENT->value => self::CASH_DISBURSEMENT->label(),
            self::PAYROLL_CASH_COLLECTED->value => self::PAYROLL_CASH_COLLECTED->label(),
            self::PAYROLL_CASH_PAID->value => self::PAYROLL_CASH_PAID->label(),
            self::CASH_TRANSFER->value => self::CASH_TRANSFER->label(),
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH_COLLECTION => 'Cash collection',
            self::CASH_DISBURSEMENT => 'Cash disbursement',
            self::PAYROLL_CASH_COLLECTED => 'Payroll: cash collected',
            self::PAYROLL_CASH_PAID => 'Payroll: cash paid',
            self::CASH_TRANSFER => 'Cash transfer',
        };
    }

}
