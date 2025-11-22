<?php

namespace App\Providers;

use App\Events\Events\Customers\AcceptedCustomerTaxExemptionEvent;
use App\Events\Events\Customers\CreateCustomerTaxExemptionEComEvent;
use App\Events\Events\Customers\CreateCustomerTaxExemptionEvent;
use App\Events\Events\Customers\CustomerGiveEcommTag;
use App\Events\Events\Customers\DeclineCustomerTaxExemptionEvent;
use App\Events\Events\Customers\DeleteCustomerTaxExemptionEvent;
use App\Events\Events\Inventories\Brands\CreateBrandEvent;
use App\Events\Events\Inventories\Brands\DeleteBrandEvent;
use App\Events\Events\Inventories\Brands\UpdateBrandEvent;
use App\Events\Events\Inventories\Categories\CreateCategoryEvent;
use App\Events\Events\Inventories\Categories\DeleteCategoryEvent;
use App\Events\Events\Inventories\Categories\UpdateImageCategoryEvent;
use App\Events\Events\Inventories\Categories\UpdateCategoryEvent;
use App\Events\Events\Inventories\Features\CreateFeatureEvent;
use App\Events\Events\Inventories\Features\DeleteFeatureEvent;
use App\Events\Events\Inventories\Features\UpdateFeatureEvent;
use App\Events\Events\Inventories\FeatureValues\CreateFeatureValueEvent;
use App\Events\Events\Inventories\FeatureValues\DeleteFeatureValueEvent;
use App\Events\Events\Inventories\FeatureValues\UpdateFeatureValueEvent;
use App\Events\Events\Inventories\Inventories\ChangeQuantityInventory;
use App\Events\Events\Inventories\Inventories\CreateInventoryEvent;
use App\Events\Events\Inventories\Inventories\UpdateImageInventoryEvent;
use App\Events\Events\Inventories\Inventories\DeleteInventoryEvent;
use App\Events\Events\Inventories\Inventories\UpdateInventoryEvent;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Events\Settings\RequestToEcom as RequestToEcomSettings;
use App\Events\Events\Users\UserChangedEvent;
use App\Events\Listeners\Customers\SendDataToHaullDepot;
use App\Events\Listeners\Customers\SyncEComAcceptedCustomerTaxExemptionListener;
use App\Events\Listeners\Customers\SyncEComCreateCustomerTaxExemptionListener;
use App\Events\Listeners\Customers\SyncEComDeclineCustomerTaxExemptionListener;
use App\Events\Listeners\Customers\SyncEComDeleteCustomerTaxExemptionListener;
use App\Events\Listeners\Inventories\Brands\SyncEComCreateBrandListener;
use App\Events\Listeners\Inventories\Brands\SyncEComDeleteBrandListener;
use App\Events\Listeners\Inventories\Brands\SyncEComUpdateBrandListener;
use App\Events\Listeners\Inventories\Categories\SyncEComCreateCategoryListener;
use App\Events\Listeners\Inventories\Categories\SyncEComDeleteCategoryListener;
use App\Events\Listeners\Inventories\Categories\SyncEComUpdateCategoryUpdateImageListener;
use App\Events\Listeners\Inventories\Categories\SyncEComUpdateCategoryImagesListener;
use App\Events\Listeners\Inventories\Categories\SyncEComUpdateCategoryListener;
use App\Events\Listeners\Inventories\Features\SyncEComCreateFeatureListener;
use App\Events\Listeners\Inventories\Features\SyncEComDeleteFeatureListener;
use App\Events\Listeners\Inventories\Features\SyncEComUpdateFeatureListener;
use App\Events\Listeners\Inventories\FeatureValues\SyncEComCreateFeatureValueListener;
use App\Events\Listeners\Inventories\FeatureValues\SyncEComDeleteFeatureValueListener;
use App\Events\Listeners\Inventories\FeatureValues\SyncEComUpdateFeatureValueListener;
use App\Events\Listeners\Inventories\Inventories\SyncEComChangeQuantityInventoryListener;
use App\Events\Listeners\Inventories\Inventories\SyncEComCreateInventoryListener;
use App\Events\Listeners\Inventories\Inventories\SyncEComDeleteInventoryListener;
use App\Events\Listeners\Inventories\Inventories\SyncEComUpdateInventoryUpdateImageListener;
use App\Events\Listeners\Inventories\Inventories\SyncEComUpdateInventoryImagesListener;
use App\Events\Listeners\Inventories\Inventories\SyncEComUpdateInventoryListener;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Events\Listeners\Settings\RequestToEcomListener as RequestToEcomListenerSettings;
use App\Events\Listeners\Users\SendNotificationChangePasswordListener;
use App\Events\Listeners\Users\SendNotificationTaxExemptionListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserChangedEvent::class => [
            SendNotificationChangePasswordListener::class
        ],
        CreateBrandEvent::class => [
            SyncEComCreateBrandListener::class
        ],
        DeleteBrandEvent::class => [
            SyncEComDeleteBrandListener::class
        ],
        UpdateBrandEvent::class => [
            SyncEComUpdateBrandListener::class
        ],
        CreateCategoryEvent::class => [
            SyncEComCreateCategoryListener::class
        ],
        UpdateCategoryEvent::class => [
            SyncEComUpdateCategoryListener::class
        ],
        UpdateImageCategoryEvent::class => [
            SyncEComUpdateCategoryUpdateImageListener::class
        ],
        UpdateImageInventoryEvent::class => [
            SyncEComUpdateInventoryUpdateImageListener::class
        ],
        DeleteCategoryEvent::class => [
            SyncEComDeleteCategoryListener::class
        ],
        CustomerGiveEcommTag::class => [
            SendDataToHaullDepot::class
        ],
        CreateFeatureEvent::class => [
            SyncEComCreateFeatureListener::class
        ],
        UpdateFeatureEvent::class => [
            SyncEComUpdateFeatureListener::class
        ],
        DeleteFeatureEvent::class => [
            SyncEComDeleteFeatureListener::class
        ],
        CreateFeatureValueEvent::class => [
            SyncEComCreateFeatureValueListener::class
        ],
        UpdateFeatureValueEvent::class => [
            SyncEComUpdateFeatureValueListener::class
        ],
        DeleteFeatureValueEvent::class => [
            SyncEComDeleteFeatureValueListener::class
        ],
        CreateInventoryEvent::class => [
            SyncEComCreateInventoryListener::class
        ],
        ConversionHasBeenCompleted::class => [
            SyncEComUpdateInventoryImagesListener::class,
            SyncEComUpdateCategoryImagesListener::class
        ],
        UpdateInventoryEvent::class => [
            SyncEComUpdateInventoryListener::class
        ],
        DeleteInventoryEvent::class => [
            SyncEComDeleteInventoryListener::class
        ],
        ChangeQuantityInventory::class => [
            SyncEComChangeQuantityInventoryListener::class
        ],
        CreateCustomerTaxExemptionEvent::class => [
            SyncEComCreateCustomerTaxExemptionListener::class
        ],
        AcceptedCustomerTaxExemptionEvent::class => [
            SyncEComAcceptedCustomerTaxExemptionListener::class
        ],
        DeclineCustomerTaxExemptionEvent::class => [
            SyncEComDeclineCustomerTaxExemptionListener::class
        ],
        DeleteCustomerTaxExemptionEvent::class => [
            SyncEComDeleteCustomerTaxExemptionListener::class
        ],
        CreateCustomerTaxExemptionEComEvent::class => [
            SendNotificationTaxExemptionListener::class
        ],
        RequestToEcom::class => [
            RequestToEcomListener::class
        ],
        RequestToEcomSettings::class => [
            RequestToEcomListenerSettings::class
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
