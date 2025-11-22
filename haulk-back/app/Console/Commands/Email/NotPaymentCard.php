<?php

namespace App\Console\Commands\Email;

use App\Models\Saas\Company\Company;
use App\Notifications\Saas\Companies\Payment\NotAddFinalRemind;
use App\Notifications\Saas\Companies\Payment\NotAddFirstRemind;
use App\Notifications\Saas\Companies\Payment\NotAddSecondRemind;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class NotPaymentCard extends Command
{
    protected $signature = 'emails:not_payment_card';
    protected $description = 'не добавили кредитную карточку';

    public function handle()
    {
        try {
            $minFirst = config('app.email.sending_in_time.not_payment_cart_first_remind');
            $minSecond = config('app.email.sending_in_time.not_payment_cart_second_remind');
            $minFinal = config('app.email.sending_in_time.not_payment_cart_final_remind');

            Company::query()
                ->whereDoesntHave('paymentMethod')
                ->where('active', true)
                ->each(function(Company $item) use ($minSecond, $minFirst, $minFinal) {

                    $now = CarbonImmutable::now();

                    $periodFirst = $now->subMinutes($minFirst);
                    $periodSecond = $now->subMinutes($minSecond);
                    $periodFinal = $now->subMinutes($minFinal);

                    if(
                        $item->created_at < $periodFirst
                        && $item->not_payment_card_count == 0
                    ){
                        $item->notify(new NotAddFirstRemind());
                        $item->update(['not_payment_card_count' => 1]);
                    } elseif(
                        $item->created_at < $periodSecond
                        && $item->not_payment_card_count == 1
                    ) {
                        $item->notify(new NotAddSecondRemind());
                        $item->update(['not_payment_card_count' => 2]);
                    } elseif(
                        $item->created_at < $periodFinal
                        && $item->not_payment_card_count == 2
                    ) {
                        $item->notify(new NotAddFinalRemind());
                        $item->update(['not_payment_card_count' => 3]);
                    }
                });

        } catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }
}
