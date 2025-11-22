<?php

namespace App\Console\Commands\Email;

use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use App\Notifications\Saas\CompanyRegistration\NotConfirmEmailFinalRemind;
use App\Notifications\Saas\CompanyRegistration\NotConfirmEmailNextDay;
use App\Notifications\Saas\CompanyRegistration\NotConfirmEmailSecondRemind;
use App\Notifications\Saas\CompanyRegistration\NotConfirmEmailThirdRemind;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class NotConfirmSignup extends Command
{
    protected $signature = 'emails:not_confirm_signup';
    protected $description = 'не потвердил имейл после подачи заявки';

    public function handle()
    {
        try {
            $minSecond = config('app.email.sending_in_time.not_confirm_second_remind');
            $minThird = config('app.email.sending_in_time.not_confirm_third_remind');
            $minFinal = config('app.email.sending_in_time.not_confirm_final_remind');

            CompanyRegistration::query()
                ->each(function(CompanyRegistration $item) use ($minSecond, $minThird, $minFinal) {

                    $now = CarbonImmutable::now();

                    $periodSecond = $now->subMinutes($minSecond);
                    $periodThird = $now->subMinutes($minThird);
                    $periodFinal = $now->subMinutes($minFinal);

                    $hash = simple_hash($item);

                    if(
                        $item->created_at->isYesterday()
                        && $item->not_confirmed_send_count == 0
                    ){
                        $item = $this->update($item, 1, $hash);
                        $item->notify(new NotConfirmEmailNextDay($hash));
                    } elseif (
                        $item->created_at < $periodSecond
                        && $item->not_confirmed_send_count == 1
                    ){
                        $item = $this->update($item, 2, $hash);
                        $item->notify(new NotConfirmEmailSecondRemind($hash));
                    } elseif (
                        $item->created_at < $periodThird
                        && $item->not_confirmed_send_count == 2
                    ){
                        $item = $this->update($item, 3, $hash);
                        $item->notify(new NotConfirmEmailThirdRemind($hash));
                    } elseif (
                        $item->created_at < $periodFinal
                        && $item->not_confirmed_send_count == 3
                    ){
                        $item = $this->update($item, 4, $hash);
                        $item->notify(new NotConfirmEmailFinalRemind($hash));
                    }
                });

        } catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }

    protected function update(CompanyRegistration $model, $count, $hash): CompanyRegistration
    {
        $model->confirmation_hash = hash('sha256', $hash);
        $model->not_confirmed_send_count = $count;
        $model->save();

        return $model;
    }
}
