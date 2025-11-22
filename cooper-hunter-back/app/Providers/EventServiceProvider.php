<?php

namespace App\Providers;

use App\Events\Admins\AdminCreatedEvent;
use App\Events\Chat\ConversationIsProcessed;
use App\Events\Commercial\DeleteCommercialProjectToOnec;
use App\Events\Commercial\RDPCredentialsGeneratedEvent;
use App\Events\Commercial\SendCommercialProjectToOnec;
use App\Events\Commercial\SendQuoteByEmailEvent;
use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\Events\Companies\UpdateCompanyByOnecEvent;
use App\Events\Dealers\CreateOrUpdateDealerEvent;
use App\Events\Dealers\DealerRegisteredEvent;
use App\Events\Favourites\FavouriteCreatedEvent;
use App\Events\Favourites\FavouriteDeletedEvent;
use App\Events\Members\MemberProfileDeletedEvent;
use App\Events\Orders\Dealer\ApprovedOrderEvent;
use App\Events\Orders\Dealer\CheckoutOrderEvent;
use App\Events\Orders\Dealer\UpdatePackingSlipEvent;
use App\Events\Orders\OrderDeletedEvent;
use App\Events\Orders\OrderSavedEvent;
use App\Events\Payments\AddPaymentCardToMemberEvent;
use App\Events\Payments\DeletePaymentCardFromMemberEvent;
use App\Events\Payments\PayPalCheckoutSavedEvent;
use App\Events\Statistics\FindSolutionStatisticEvent;
use App\Events\SupportRequests\SupportRequestCreatedEvent;
use App\Events\SupportRequests\SupportRequestMessageSavedEvent;
use App\Events\SupportRequests\SupportRequestUpdatedEvent;
use App\Events\Systems\SystemUpdatedEvent;
use App\Events\Technicians\TechnicianRegisteredEvent;
use App\Events\Technicians\TechnicianUpdatedEvent;
use App\Events\Users\UserRegisteredEvent;
use App\Events\Users\UserUpdatedEvent;
use App\Events\Warranty\WarrantyRegistrationProcessedEvent;
use App\Events\Warranty\WarrantyRegistrationRequestedEvent;
use App\Listeners\Admins\AdminCreatedListener;
use App\Listeners\Alerts\AlertEventsListener;
use App\Listeners\Chat\ConversationIsProcessedListener;
use App\Listeners\Chat\ConversationStartedListener;
use App\Listeners\Chat\MessageWasSentListener;
use App\Listeners\Commercial\DeleteCommercialProjectToOnecListener;
use App\Listeners\Commercial\RDPCredentialsGeneratedListener;
use App\Listeners\Commercial\SendCommercialProjectToOnecListener;
use App\Listeners\Commercial\SendQuoteByEmailListener;
use App\Listeners\Companies\SendCodeForDealerListener;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Listeners\Dealers\DealerRegisteredListener;
use App\Listeners\Dealers\DealerRegisteredSetRoleListener;
use App\Listeners\Dealers\DealerSendCredentialsListener;
use App\Listeners\Favourites\FavouriteSubscriptionListener;
use App\Listeners\Members\MemberProfileDeletedListener;
use App\Listeners\Members\MemberSubscriptionListener;
use App\Listeners\Orders\Dealer\SendDataToOnecListener;
use App\Listeners\Orders\Dealer\SendEmailToCompanyManagerListener;
use App\Listeners\Orders\Dealer\SendEmailToDealerAsApprovedOrderListener;
use App\Listeners\Orders\OrderDeletedListener;
use App\Listeners\Orders\OrderSavedListener;
use App\Listeners\Payments\PayPalCheckoutSavedListener;
use App\Listeners\Payments\SendPaymentCardToOnecListeners;
use App\Listeners\Statistics\FindSolutionStatisticListener;
use App\Listeners\SupportRequests\SupportRequestSubscriptionListener;
use App\Listeners\Technicians\TechnicianRegisteredListener;
use App\Listeners\Technicians\TechnicianRegisteredSetRoleListener;
use App\Listeners\Technicians\TechnicianUpdatedListener;
use App\Listeners\Users\UserRegisteredListener;
use App\Listeners\Users\UserRegisteredSetRoleListener;
use App\Listeners\Warranty\WarrantyRegistrationProcessedListener;
use App\Listeners\Warranty\WarrantyRegistrationRequestedListener;
use Core\Chat\Events\ConversationStarted;
use Core\Chat\Events\MessageWasSent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserRegisteredEvent::class => [
            UserRegisteredListener::class,
            UserRegisteredSetRoleListener::class,
            AlertEventsListener::class,
        ],
        SendCommercialProjectToOnec::class => [
            SendCommercialProjectToOnecListener::class
        ],
        DeleteCommercialProjectToOnec::class => [
            DeleteCommercialProjectToOnecListener::class
        ],
        UserUpdatedEvent::class => [
            MemberSubscriptionListener::class,
        ],
        TechnicianRegisteredEvent::class => [
            TechnicianRegisteredListener::class,
            TechnicianRegisteredSetRoleListener::class,
            AlertEventsListener::class,
        ],
        DealerRegisteredEvent::class => [
            DealerRegisteredListener::class,
            DealerRegisteredSetRoleListener::class,
            AlertEventsListener::class,
        ],
        TechnicianUpdatedEvent::class => [
            TechnicianUpdatedListener::class,
            AlertEventsListener::class,
            MemberSubscriptionListener::class,
        ],
        MemberProfileDeletedEvent::class => [
            MemberProfileDeletedListener::class,
        ],
        ConversationStarted::class => [
            ConversationStartedListener::class,
        ],
        MessageWasSent::class => [
            MessageWasSentListener::class,
        ],
        ConversationIsProcessed::class => [
            ConversationIsProcessedListener::class,
        ],
        RDPCredentialsGeneratedEvent::class => [
            RDPCredentialsGeneratedListener::class,
        ],
        OrderSavedEvent::class => [
            OrderSavedListener::class,
            AlertEventsListener::class,
        ],
        OrderDeletedEvent::class => [
            OrderDeletedListener::class,
        ],
        PayPalCheckoutSavedEvent::class => [
            PayPalCheckoutSavedListener::class,
        ],
        FindSolutionStatisticEvent::class => [
            FindSolutionStatisticListener::class,
        ],
        AdminCreatedEvent::class => [
            AdminCreatedListener::class,
        ],
        SupportRequestMessageSavedEvent::class => [
            AlertEventsListener::class,
            SupportRequestSubscriptionListener::class,
        ],
        SupportRequestUpdatedEvent::class => [
            AlertEventsListener::class,
            SupportRequestSubscriptionListener::class,
        ],
        SupportRequestCreatedEvent::class => [
            SupportRequestSubscriptionListener::class,
        ],
        SystemUpdatedEvent::class => [
            AlertEventsListener::class,
        ],
        FavouriteCreatedEvent::class => [
            FavouriteSubscriptionListener::class,
        ],
        FavouriteDeletedEvent::class => [
            FavouriteSubscriptionListener::class,
        ],
        WarrantyRegistrationRequestedEvent::class => [
            WarrantyRegistrationRequestedListener::class,
        ],
        WarrantyRegistrationProcessedEvent::class => [
            WarrantyRegistrationProcessedListener::class,
        ],
        SendQuoteByEmailEvent::class => [
            SendQuoteByEmailListener::class,
        ],
        CreateOrUpdateDealerEvent::class => [
            DealerSendCredentialsListener::class,
            DealerRegisteredSetRoleListener::class,
        ],
        CreateOrUpdateCompanyEvent::class => [
            SendDataToOnecListeners::class,
        ],
        UpdateCompanyByOnecEvent::class => [
            SendCodeForDealerListener::class,
        ],
        AddPaymentCardToMemberEvent::class => [
            SendPaymentCardToOnecListeners::class,
        ],
        DeletePaymentCardFromMemberEvent::class => [
            SendPaymentCardToOnecListeners::class,
        ],
        UpdatePackingSlipEvent::class => [
            SendDataToOnecListener::class,
        ],
        CheckoutOrderEvent::class => [
            SendEmailToCompanyManagerListener::class,
        ],
        ApprovedOrderEvent::class => [
            SendEmailToDealerAsApprovedOrderListener::class,
        ],
    ];

    public function listens(): array
    {
        return parent::listens()
            + config('events.default');
    }
}
