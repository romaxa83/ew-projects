<?php

namespace App\Console\Commands\Billing;

use App\Models\Billing\Invoice;
use App\Notifications\Billing\BlockedAccount;
use App\Notifications\Billing\FailPaid;
use App\Notifications\Billing\SuccessPaidForSuperAdmin;
use App\Services\Billing\BillingService;
use App\Services\Permissions\Payments\PaymentProviderInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class UpdatePendingTransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:update-pending-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var $billingService BillingService */
        $billingService = resolve(BillingService::class);
        /** @var $paymentService PaymentProviderInterface */
        $paymentService = resolve(PaymentProviderInterface::class);

        $invoices = Invoice::withoutGlobalScopes()
            ->where('pending', true)
            ->oldest()
            ->take(config('billing.invoices.process_per_cycle'))
            ->get();

        $invoices->each(
            function (Invoice $invoice) use ($billingService, $paymentService) {
                if ($invoice->trans_id) {
                    $status = $paymentService->getTransactionStatus($invoice->trans_id);

                    if ($status === PaymentProviderInterface::TRANSACTION_APPROVED) {
                        $billingService->markInvoicePaymentPaid($invoice);
                        Notification::route('mail', $invoice->company->getSuperAdmin()->email)
                            ->notify(new SuccessPaidForSuperAdmin($invoice));
                        return;
                    }

                    if ($status === PaymentProviderInterface::TRANSACTION_DECLINED) {
                        $billingService->markInvoicePaymentNotPaid($invoice);

                        $contactData = $invoice->company->getPaymentContactData();

                        if ($invoice->chargeAttemptsExhausted()) {
                            Notification::route('mail', $contactData['email'])->notify(new BlockedAccount($contactData['full_name']));
                            Notification::route('mail', config('billing.info_email'))->notify(new BlockedAccount($contactData['full_name']));
                        } else {
                            Notification::route('mail', $contactData['email'])->notify(new FailPaid($invoice));
                            Notification::route('mail', config('billing.info_email'))->notify(new FailPaid($invoice));
                        }

                        return;
                    }
                }
            }
        );

        return 0;
    }
}
