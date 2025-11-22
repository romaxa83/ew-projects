<?php


namespace App\Services\Events;


use App\Models\Admins\Admin;
use App\Models\BodyShop\Inventories\Inventory;
use App\Models\Contacts\Contact;
use App\Models\Fueling\FuelingHistory;
use App\Models\GPS\Alert;
use App\Models\Library\LibraryDocument;
use App\Models\News\News;
use App\Models\Orders\Order;
use App\Models\Orders\OrderComment;
use App\Models\Payrolls\Payroll;
use App\Models\Reports\DriverTripReport;
use App\Models\Saas\Company\Company;
use App\Models\Saas\Company\CompanyInsuranceInfo;
use App\Models\Saas\Company\CompanyNotificationSettings;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceRequest;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use App\Services\Events\Billing\BillingEventService;
use App\Services\Events\BodyShop\Inventory\InventoryEventService;
use App\Services\Events\Carrier\CarrierEventService;
use App\Services\Events\Carrier\CarrierInsuranceEventService;
use App\Services\Events\Carrier\CarrierNotificationEventService;
use App\Services\Events\Contact\ContactEventService;
use App\Services\Events\Fueling\FuelingHistoryEventService;
use App\Services\Events\GPS\Alerts\GpsAlertEventService;
use App\Services\Events\GPS\Devices\DeviceApproveActivityEventService;
use App\Services\Events\GPS\Devices\DeviceRequestEventService;
use App\Services\Events\GPS\Devices\DeviceSubscriptionEventService;
use App\Services\Events\Library\LibraryEventService;
use App\Services\Events\News\NewsEventService;
use App\Services\Events\Order\OrderCommentEventService;
use App\Services\Events\Order\OrderEventService;
use App\Services\Events\Order\OrderStatusEventService;
use App\Services\Events\Payroll\PayrollEventService;
use App\Services\Events\Reports\DriverTripReportEventService;
use App\Services\Events\Support\SupportEventService;
use App\Services\Events\User\UserEventService;
use App\Services\Events\BodyShop\Order\OrderEventService as OrderEventServiceBS;
use App\Services\Events\BodyShop\Order\OrderCommentEventService as OrderCommentEventServiceBS;
use App\Models\BodyShop\Orders\Order as OrderBS;
use App\Models\BodyShop\Orders\OrderComment as OrderCommentBS;
use App\Services\Events\Vehicle\VehicleEventService;
use App\Services\Push\PushService;

/**
 * Class EventService
 * @package App\Services\Events
 * @method OrderEventService order(Order|null $order = null);
 * @method OrderStatusEventService status(Order $order);
 * @method OrderCommentEventService comment(Order $order, OrderComment|null $comment = null);
 * @method CarrierEventService carrier(Company $company);
 * @method CarrierInsuranceEventService carrierInsurance(CompanyInsuranceInfo $insurance);
 * @method CarrierNotificationEventService carrierNotification(CompanyNotificationSettings $notification);
 * @method NewsEventService news(News $news);
 * @method ContactEventService contact(Contact $contact);
 * @method UserEventService users(User $user);
 * @method LibraryEventService library(LibraryDocument $document)
 * @method BillingEventService billing(Company $company)
 * @method PayrollEventService payroll(Payroll $payroll)
 * @method DriverTripReportEventService driverTripReport(DriverTripReport $driverTripReport)
 * @method SupportEventService support(SupportRequest $supportRequest)
 * @method DeviceRequestEventService deviceRequest(DeviceRequest $deviceRequest)
 * @method DeviceSubscriptionEventService deviceSubscription(DeviceSubscription $deviceSubscription)
 * @method GpsAlertEventService gpsAlert(Alert $alert)
 * @method FuelingHistoryEventService fuelingHistory(FuelingHistory $fuelingHistory)
 * @method DeviceApproveActivityEventService deviceToggleActivity(Device $device)
 * @method InventoryEventService bsInventory(Inventory $inventory, ?string $comment = null, ?OrderBS $order = null, ?float $price = null)
 * @method OrderEventServiceBS bsOrder(OrderBS $order)
 * @method OrderCommentEventServiceBS bsComment(OrderBS $order, OrderCommentBS|null $comment = null);
 * @method VehicleEventService truck(Vehicle $vehicle);
 */
abstract class EventService
{
    protected const ACTION_CREATE = 'create';
    protected const ACTION_UPDATE = 'update';
    protected const ACTION_DELETE = 'delete';

    /**@var Admin|User|null $user*/
    protected $user;

    protected string $action;

    private ?PushService $pushService = null;

    private const SERVICES_LIST = [
        'order' => OrderEventService::class,
        'status' => OrderStatusEventService::class,
        'comment' => OrderCommentEventService::class,
        'carrier' => CarrierEventService::class,
        'carrierInsurance' => CarrierInsuranceEventService::class,
        'carrierNotification' => CarrierNotificationEventService::class,
        'news' => NewsEventService::class,
        'contact' => ContactEventService::class,
        'users' => UserEventService::class,
        'library' => LibraryEventService::class,
        'billing' => BillingEventService::class,
        'payroll' => PayrollEventService::class,
        'driverTripReport' => DriverTripReportEventService::class,
        'support' => SupportEventService::class,
        'bsInventory' => InventoryEventService::class,
        'bsOrder' => OrderEventServiceBS::class,
        'bsComment' =>  OrderCommentEventServiceBS::class,
        'vehicle' => VehicleEventService::class,
        'deviceRequest' => DeviceRequestEventService::class,
        'deviceSubscription' => DeviceSubscriptionEventService::class,
        'deviceToggleActivity' => DeviceApproveActivityEventService::class,
        'gpsAlert' => GpsAlertEventService::class,
        'fuelingHistory' => FuelingHistoryEventService::class,
    ];

    public static function __callStatic($name, $arguments)
    {
        if (array_key_exists($name, self::SERVICES_LIST)) {
            $class = self::SERVICES_LIST[$name];
            return new $class(...$arguments);
        }
        return null;
    }

    /**
     * @param Admin|User $user
     * @return $this
     */
    public function user($user): EventService
    {
        $this->user = $user;
        return $this;
    }

    protected function pushService(): PushService
    {
        if ($this->pushService !== null) {
            return $this->pushService;
        }
        $this->pushService = resolve(PushService::class);
        return $this->pushService;
    }

    public function custom(string $action): EventService
    {
        $this->action = $action;
        return $this;
    }

    public function create(): EventService
    {
        $this->action = self::ACTION_CREATE;

        return $this;
    }

    public function update(): EventService
    {
        $this->action = self::ACTION_UPDATE;

        return $this;
    }

    public function delete(): EventService
    {
        $this->action = self::ACTION_DELETE;

        return $this;
    }
}
