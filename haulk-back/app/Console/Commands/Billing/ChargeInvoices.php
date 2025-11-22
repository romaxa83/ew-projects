<?php

namespace App\Console\Commands\Billing;

use App\Exceptions\Billing\TransactionUnderReviewException;
use App\Models\Billing\Invoice;
use App\Notifications\Billing\BlockedAccount;
use App\Notifications\Billing\FailPaid;
use App\Notifications\Billing\TransactionUnderReview;
use App\Notifications\Saas\Invoices\InvoicePaymentPending;
use App\Services\Billing\BillingService;
use App\Services\Events\EventService;
use App\Services\Saas\BackofficeService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ChargeInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:charge-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     */
    public function handle(): void
    {
        /** @var $billingService BillingService */
        $billingService = resolve(BillingService::class);
        /** @var $backofficeService BackofficeService */
        $backofficeService = resolve(BackofficeService::class);

        $invoices = Invoice::withoutGlobalScopes()
            ->where(
                [
                    ['is_paid', false],
                    ['pending', false],
                    ['attempt', '<', config('billing.invoices.max_charge_attempts')],
                    ['next_attempt_time', '<', now()->timestamp],
                ]
            )
            ->oldest()
            ->take(config('billing.invoices.process_per_cycle'))
            ->get();

        $invoices->each(
            function (Invoice $invoice) use ($billingService, $backofficeService) {
                try {
                    $billingService->chargeInvoice($invoice);
                } catch (TransactionUnderReviewException $e) {
                    $billingService->markInvoicePaymentPending($invoice, $e->getTransID());

                    $contactData = $invoice->company->getPaymentContactData();

                    Notification::route('mail', $contactData['email'])->notify(new TransactionUnderReview($invoice, $e->getTransID()));
                    Notification::route('mail', config('billing.info_email'))->notify(new TransactionUnderReview($invoice, $e->getTransID()));
                    Notification::route('mail', $backofficeService->getSuperAdmin()->email)->notify(new InvoicePaymentPending($e->getTransID()));
                } catch (Exception $e) {
                    $billingService->markInvoicePaymentFailed($invoice, $e->getMessage());

                    $contactData = $invoice->company->getPaymentContactData();

                    if ($invoice->chargeAttemptsExhausted()) {
                        Notification::route('mail', $contactData['email'])->notify(new BlockedAccount($contactData['full_name']));
                        Notification::route('mail', config('billing.info_email'))->notify(new BlockedAccount($contactData['full_name']));
                    } else {
                        Notification::route('mail', $contactData['email'])->notify(new FailPaid($invoice));
                        Notification::route('mail', config('billing.info_email'))->notify(new FailPaid($invoice));
                    }
                }

                EventService::billing($invoice->company)
                    ->update()
                    ->broadcast();
            }
        );
    }
}
