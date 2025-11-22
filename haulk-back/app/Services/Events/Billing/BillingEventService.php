<?php


namespace App\Services\Events\Billing;

use App\Broadcasting\Events\Subscription\SubscriptionSubscribeBroadcast;
use App\Broadcasting\Events\Subscription\SubscriptionUnsubscribeBroadcast;
use App\Broadcasting\Events\Subscription\SubscriptionUpdateBroadcast;
use App\Models\Saas\Company\Company;
use App\Services\Events\EventService;

class BillingEventService extends EventService
{

    private const ACTION_SUBSCRIBE = 'subscribe';
    private const ACTION_UNSUBSCRIBE = 'unsubscribe';

    private const BROADCASTING_EVENTS = [
        self::ACTION_UPDATE => SubscriptionUpdateBroadcast::class,
        self::ACTION_SUBSCRIBE => SubscriptionSubscribeBroadcast::class,
        self::ACTION_UNSUBSCRIBE => SubscriptionUnsubscribeBroadcast::class
    ];

    private Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function subscribe(): BillingEventService
    {
        $this->action = self::ACTION_SUBSCRIBE;

        return $this;
    }

    public function unsubscribe(): BillingEventService
    {
        $this->action = self::ACTION_UNSUBSCRIBE;

        return $this;
    }

    public function broadcast(): BillingEventService
    {
        $broadcast = self::BROADCASTING_EVENTS[$this->action];

        event(new $broadcast($this->company->id));

        return $this;
    }
}
