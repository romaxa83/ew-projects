<?php

namespace App\Console\Commands\Email;

use App\Models\Saas\Company\Company;
use App\Notifications\Saas\Companies\Login\NotLoginFinalRemind;
use App\Notifications\Saas\Companies\Login\NotLoginFirstRemind;
use App\Notifications\Saas\Companies\Login\NotLoginSecondRemind;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class NotLoginFreeTrial extends Command
{
    protected $signature = 'emails:not_login';
    protected $description = 'не заходит в систему но при этом имеет бесплатный доступ';

    public function handle()
    {
        try {
            $minFirst = config('app.email.sending_in_time.not_login_free_trial_first_remind');
            $minSecond = config('app.email.sending_in_time.not_login_free_trial_second_remind');
            $minFinal = config('app.email.sending_in_time.not_login_free_trial_final_remind');

            Company::query()
                ->where('active', true)
                ->whereHas('paymentMethod')
                ->whereHas('subscription', fn ($q) => $q->where('pricing_plan_id', config('pricing.plans.trial.id')))
                ->each(function(Company $item) use ($minSecond, $minFirst, $minFinal) {

                    if($login = $item->getSuperAdmin()->lastLogin){

                        $now = CarbonImmutable::now();

                        if(
                            $login->created_at->diffInMinutes($now) > $minFirst
                            && $item->not_login_free_trial_count == 0
                        ){
                            $item->notify(new NotLoginFirstRemind());
                            $item->update(['not_login_free_trial_count' => 1]);
                        } elseif(
                            $login->created_at->diffInMinutes($now) > $minSecond
                            && $item->not_login_free_trial_count == 1
                        ) {
                            $item->notify(new NotLoginSecondRemind());
                            $item->update(['not_login_free_trial_count' => 2]);

                        } elseif (
                            $login->created_at->diffInMinutes($now) > $minFinal
                            && $item->not_login_free_trial_count == 2
                        ) {
                            $item->notify(new NotLoginFinalRemind());
                            $item->update(['not_login_free_trial_count' => 3]);
                        }
                    }
                });

        } catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }
}
