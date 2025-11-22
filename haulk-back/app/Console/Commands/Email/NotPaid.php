<?php

namespace App\Console\Commands\Email;

use App\Models\Billing\Invoice;
use App\Models\Saas\Company\Company;
use App\Notifications\Billing\AfterNotPaidFirstRemind;
use App\Notifications\Billing\AfterNotPaidSecondRemind;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class NotPaid extends Command
{
    protected $signature = 'emails:not_paid';
    protected $description = 'оповещение если не было оплаты';

    public function handle()
    {
        try {
            $minFirst = config('app.email.sending_in_time.not_paid_first_remind');
            $minSecond = config('app.email.sending_in_time.not_paid_second_remind');

            $now = CarbonImmutable::now();

            Company::query()
                ->where('active', true)
                ->whereHas('invoices', function ($b){
                    $b->where('is_paid', false)
                        ->where('attempt', 3)
                        ->where('count_send_not_paid', '!=', 2)
                    ;
                })
                ->each(function(Company $item) use($minFirst, $minSecond, $now) {

                    foreach (
                        $item->invoices()
                            ->where('is_paid', false)
                            ->where('attempt', 3)
                            ->where('count_send_not_paid', '!=', 2)
                            ->whereNotNull('last_attempt_time')
                            ->get() as $invoice
                    ){
                        /** @var $invoice Invoice */

                        $lastDate = CarbonImmutable::createFromTimestamp($invoice->last_attempt_time);

                        if(
                            $lastDate->lt($now->subMinutes($minFirst))
                            && $invoice->count_send_not_paid == 0
                        ){
                            $item->notify(new AfterNotPaidFirstRemind());
                            $invoice->update(['count_send_not_paid' => 1]);
                        } elseif (
                            $lastDate->lt($now->subMinutes($minSecond))
                            && $invoice->count_send_not_paid == 1
                        ) {
                            $item->notify(new AfterNotPaidSecondRemind());
                            $invoice->update(['count_send_not_paid' => 2]);
                        }
                    }
                });

        } catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }
}

