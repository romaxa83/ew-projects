<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\Client;
use App\Models\Vapi;
use App\Models\Communications\ConversationFavorites;
use App\Models\Communications\ConversationMark;
use App\Models\DispatchEmployer;
use App\Models\DispatchTruck;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Material;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Tasks\Task;
use App\Models\Truck\Truck;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use App\Observers\GmailMessageObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\{Collection, ServiceProvider};
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Message::observe(GmailMessageObserver::class);

        $this->registerMorphMap();

        Schema::defaultStringLength(191);
        Collection::macro('recursive', function (): Collection {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect((object)$value)->recursive();
                }
                return $value;
            });
        });
    }

    protected function registerMorphMap(): void
    {
        Relation::morphMap(self::morphs());
    }

    public static function morphs(): array
    {
        return [
            Truck::MORPH_NAME => Truck::class,
            EventAfterCall::MORPH_NAME => EventAfterCall::class,
            Client\Messenger::MORPH_NAME => Client\Messenger::class,
            Client\Notes::MORPH_NAME => Client\Notes::class,
            Client\Phone::MORPH_NAME => Client\Phone::class,
            Client\Email::MORPH_NAME => Client\Email::class,
            Client\Activity::MORPH_NAME => Client\Activity::class,
            Client::MORPH_NAME => Client::class,
            TwilioSms::MORPH_NAME => TwilioSms::class,
            CallsEvents::MORPH_NAME => CallsEvents::class,
            SmsEvents::MORPH_NAME => SmsEvents::class,
            Message::MORPH_NAME => Message::class,
            ConversationFavorites::MORPH_NAME => ConversationFavorites::class,
            ConversationMark::MORPH_NAME => ConversationMark::class,
            Order::MORPH_NAME => Order::class,
            Order\Notes::MORPH_NAME => Order\Notes::class,
            Order\Activity::MORPH_NAME => Order\Activity::class,
            Order\Work::MORPH_NAME => Order\Work::class,
            Order\Waypoint::MORPH_NAME => Order\Waypoint::class,
            Order\WaypointNotes::MORPH_NAME => Order\WaypointNotes::class,
            Order\Estimate::MORPH_NAME => Order\Estimate::class,
            Order\Estimate\Interstate::MORPH_NAME => Order\Estimate\Interstate::class,
            Order\Estimate\Intrastate::MORPH_NAME => Order\Estimate\Intrastate::class,
            Order\Estimate\Local::MORPH_NAME => Order\Estimate\Local::class,
            Order\Estimate\Calculated::MORPH_NAME => Order\Estimate\Calculated::class,
            Order\Payment::MORPH_NAME => Order\Payment::class,
            Order\Inventory::MORPH_NAME => Order\Inventory::class,
            Order\InventoryActivity::MORPH_NAME => Order\InventoryActivity::class,
            Order\Material::MORPH_NAME => Order\Material::class,
            Order\CustomExtra::MORPH_NAME => Order\CustomExtra::class,
            Order\Extended::MORPH_NAME => Order\Extended::class,
            Order\Payroll\Payroll::MORPH_NAME => Order\Payroll\Payroll::class,
            Order\Payroll\Item::MORPH_NAME => Order\Payroll\Item::class,
            Task::MORPH_NAME => Task::class,
            Attachment::MORPH_NAME => Attachment::class,
            Material::MORPH_NAME => Material::class,
            DispatchEmployer::MORPH_NAME => DispatchEmployer::class,
            DispatchTruck::MORPH_NAME => DispatchTruck::class,
            Vapi\ClientRequest::MORPH_NAME => Vapi\ClientRequest::class,
            Vapi\CallEvent::MORPH_NAME => Vapi\CallEvent::class,
        ];
    }
}
