<?php

namespace App\Listeners\Commercial;

use App\Events\Commercial\SendQuoteByEmailEvent;
use App\Models\Commercial\CommercialSettings;
use App\Notifications\Commercial\CommercialQuoteNotification;
use App\Services\Commercial\CommercialQuoteService;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendQuoteByEmailListener implements ShouldQueue
{
    public function handle(SendQuoteByEmailEvent $event): void
    {
        $service = app(CommercialQuoteService::class);

        $settings = CommercialSettings::first();

        $data = collect([
            'model' => $event->getCommercialQuote(),
            'setting' => $settings
        ]);

        try {
            Notification::route('mail', $event->getCommercialQuote()->email)
                ->notify(
                    new CommercialQuoteNotification(
                        $event->getCommercialQuote(),
                        $service->getPdfOutput(
                            $data,
                            trans('Commercial quote')
                        )
                    )
                );
        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }
}
