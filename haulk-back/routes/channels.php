<?php

use App\Broadcasting\Channels\Alerts\CompanyAlertsChannel;
use App\Broadcasting\Channels\Alerts\UserAlertsChannel;
use App\Broadcasting\Channels\CarrierChannel;
use App\Broadcasting\Channels\ContactChannel;
use App\Broadcasting\Channels\Fueling\FuelingHistory\FuelingHistoryChannel;
use App\Broadcasting\Channels\GPS\Alerts\GpsAlertChannel;
use App\Broadcasting\Channels\GPS\Device\DeviceChannel;
use App\Broadcasting\Channels\GPS\Device\Request\DeviceRequestChannel;
use App\Broadcasting\Channels\GPS\Device\Subscription\DeviceSubscriptionChannel;
use App\Broadcasting\Channels\NewsChannel;
use App\Broadcasting\Channels\SubscriptionChannel;
use App\Broadcasting\Channels\OfferChannel;
use App\Broadcasting\Channels\OrderChannel;
use App\Broadcasting\Channels\Support\Backoffice\SupportAdminChannel;
use App\Broadcasting\Channels\Support\Backoffice\SupportChannel as BackofficeSupportChannel;
use App\Broadcasting\Channels\Support\Crm\SupportChannel;
use App\Broadcasting\Channels\Support\Crm\SupportUserChannel;
use App\Broadcasting\Channels\UserChannel;
use App\Broadcasting\Channels\LibraryChannel;
use App\Broadcasting\Channels\PayrollChannel;
use App\Broadcasting\Channels\DriverTripReportChannel;


Broadcast::channel('alerts.{companyId}.user.{userId}', UserAlertsChannel::class, ['guards' => 'api']);
Broadcast::channel('alerts.{companyId}', CompanyAlertsChannel::class, ['guards' => 'api']);
Broadcast::channel('offers.{companyId}', OfferChannel::class, ['guards' => 'api']);
Broadcast::channel('orders.{companyId}', OrderChannel::class, ['guards' => 'api']);
Broadcast::channel('subscription.{companyId}', SubscriptionChannel::class, ['guards' => 'api']);
Broadcast::channel('carriers.{companyId}', CarrierChannel::class, ['guards' => 'api']);
Broadcast::channel('news.{companyId}', NewsChannel::class, ['guards' => 'api']);
Broadcast::channel('contacts.{companyId}', ContactChannel::class, ['guards' => 'api']);
Broadcast::channel('users.{companyId}', UserChannel::class, ['guards' => 'api']);
Broadcast::channel('libraries.{companyId}', LibraryChannel::class, ['guards' => 'api']);
Broadcast::channel('payrolls.{companyId}', PayrollChannel::class, ['guards' => 'api']);
Broadcast::channel('driver-trip-reports.{companyId}', DriverTripReportChannel::class, ['guards' => 'api']);
Broadcast::channel('support.{company}', SupportChannel::class, ['guards' => ['api']]);
Broadcast::channel('support.{company}.user.{user}', SupportUserChannel::class, ['guards' => ['api']]);
Broadcast::channel('device-request.{company}', DeviceRequestChannel::class, ['guards' => ['api']]);
Broadcast::channel('device.{company}', DeviceChannel::class, ['guards' => ['api']]);
Broadcast::channel('device-subscription.{company}', DeviceSubscriptionChannel::class, ['guards' => ['api']]);
Broadcast::channel('gps-alerts.{company}', GpsAlertChannel::class, ['guards' => ['api']]);
Broadcast::channel('fueling-history.{company}', FuelingHistoryChannel::class, ['guards' => ['api']]);


Broadcast::channel('support', BackofficeSupportChannel::class, ['guards' => ['api_admin']]);
Broadcast::channel('support.admin.{user}', SupportAdminChannel::class, ['guards' => ['api_admin']]);
