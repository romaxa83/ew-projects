<?php

namespace App\Console\Commands\Email;

use App\Models\Saas\Company\Company;
use App\Notifications\Billing\BeforeTrialEnd as BeforeTrialEndNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class BeforeTrialEnd extends Command
{
    protected $signature = 'emails:before_trial_end';
    protected $description = 'оповещение за день до конца бесплатного периода';

    public function handle()
    {
        try {
            $min = config('app.email.sending_in_time.before_trial_end');
            $now = CarbonImmutable::now();
            $dateFuture = $now->addMinutes($min);

            Company::query()
                ->where('active', true)
                ->where('send_before_trial', false)
                ->whereHas('subscription', function($b) use ($dateFuture, $now) {
                    $b->where('is_trial', true)
                        ->whereBetween('billing_end',[$now, $dateFuture])
                        ;
                })
                ->each(function(Company $item) use ($min) {
                    $item->notify(new BeforeTrialEndNotification());
                    $item->update(['send_before_trial' => true]);
                });

        } catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }
}
