<?php


namespace App\Listeners\SupportRequests;


use App\Contracts\Subscriptions\SupportRequestSubscriptionEvent;
use App\Enums\SupportRequests\SupportRequestSubscriptionActionEnum;
use App\GraphQL\Subscriptions\BackOffice\SupportRequests\SupportRequestSubscription as BackSubscription;
use App\GraphQL\Subscriptions\FrontOffice\SupportRequests\SupportRequestSubscription as FrontSubscription;
use App\Models\Admins\Admin;
use App\Models\Support\SupportRequestMessage;

class SupportRequestSubscriptionListener
{

    public function __construct()
    {
    }

    public function handle(SupportRequestSubscriptionEvent $event): void
    {
        $request = $event->getSupportRequest();

        $sender = $event->getSender();

        $action = $event->getAction();

        if ($action === null) {
            return;
        }

        if ($action === SupportRequestSubscriptionActionEnum::CLOSED) {
            FrontSubscription::notify()
                ->toUser($sender)
                ->withContext(
                    [
                        'support_request' => $request->id,
                        'action' => SupportRequestSubscriptionActionEnum::CLOSED
                    ]
                )
                ->broadcast();
            return;
        }

        if ($action === SupportRequestSubscriptionActionEnum::CREATED) {
            BackSubscription::notify()
                ->withContext(
                    [
                        'support_request' => $request->id,
                        'action' => SupportRequestSubscriptionActionEnum::CREATED
                    ]
                )
                ->broadcast();
            return;
        }
        //ADDED_MESSAGE
        if ($sender instanceof Admin) {
            FrontSubscription::notify()
                ->toUser($request->technician)
                ->withContext(
                    [
                        'support_request' => $request->id,
                        'action' => SupportRequestSubscriptionActionEnum::ADDED_MESSAGE
                    ]
                )
                ->broadcast();

            return;
        }
        $admins = SupportRequestMessage::select('sender_id')
            ->where('support_request_id', $request->id)
            ->where('sender_type', Admin::MORPH_NAME)
            ->groupBy('sender_id')
            ->get()
            ->pluck('sender_id')
            ->toArray();

        if (empty($admins)) {
            return;
        }
        Admin::whereIn('id', $admins)
            ->get()
            ->each(
                fn(Admin $admin) => BackSubscription::notify()
                    ->toUser($admin)
                    ->withContext(
                        [
                            'support_request' => $request->id,
                            'action' => SupportRequestSubscriptionActionEnum::ADDED_MESSAGE
                        ]
                    )
                    ->broadcast()
            );
    }
}
