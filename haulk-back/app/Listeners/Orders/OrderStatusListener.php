<?php

namespace App\Listeners\Orders;

use App\Events\ModelChanged;
use App\Events\OrderStatusChanged;
use App\Models\Orders\Order;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderPickedUp;
use App\Services\Orders\GeneratePdfService;
use Exception;
use Illuminate\Support\Facades\Notification;
use Log;
use Throwable;

class OrderStatusListener
{
    private GeneratePdfService $generatePdfService;

    public function __construct(GeneratePdfService $generatePdfService)
    {
        $this->generatePdfService = $generatePdfService;
    }

    /**
     * @param OrderStatusChanged $event
     * @throws Throwable
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        $company = $order->user->getCompany();

        $recipients = $this->getEmailRecipients($order);

        logger_info('ORDER STATUS LISTENER', [
            '$recipients' => $recipients
        ]);

        if (isset($order->shipper_contact['email']) && $order->shipper_contact['email']) {
            if ($company && $company->notificationSettings) {
                $notificationSettings = $company->notificationSettings->toArray();
                if (isset($notificationSettings['brokers_delivery_notification']) && $notificationSettings['brokers_delivery_notification']) {
                    $recipients[] = $order->shipper_contact['email'];
                }
            }
        }

        if ($order->isStatusPickedUp()) {
            $this->generatePdfService->sendDocsDelayed($order, 'pickup');

            if ($recipients) {
                Notification::route('mail', $recipients)->notify(new OrderPickedUp($order));
            }
        }

        if ($order->isStatusDelivered()) {
            $this->generatePdfService->sendDocsDelayed($order, 'delivery');

            if ($recipients) {
                Notification::route('mail', $recipients)->notify(new OrderDelivered($order));
            }

            if ($company && $company->notificationSettings) {
                $notificationSettings = $company->notificationSettings->toArray();

                if (isset($notificationSettings['send_bol_invoice_automatically']) && $notificationSettings['send_bol_invoice_automatically']) {
                    $this->generatePdfService->sendInvoiceAutomatic($order);
                    $this->sendBol($order, $notificationSettings);
                }
            }
        }
    }

    protected function getEmailRecipients(Order $order): array
    {
        $recipients = [];
        $company = $order->user->getCompany();

        if ($company && $company->notificationSettings) {
            $notificationSettings = $company->notificationSettings->toArray();

            if (!isset($notificationSettings['notification_emails'])) {
                return $recipients;
            }

            if (is_array($notificationSettings['notification_emails'])) {
                foreach ($notificationSettings['notification_emails'] as $el) {
                    if (isset($el['value']) && $el['value']) {
                        $recipients[] = $el['value'];
                    }
                }
            }
        }

        return $recipients;
    }

    /**
     * @param Order $order
     * @param array $notificationSettings
     * @throws Throwable
     */
    protected function sendBol(Order $order, array $notificationSettings): void
    {
        try {
            $this->generatePdfService->sendBolAutomatic($order, $notificationSettings);
        } catch (Exception $e) {
            event(
                new ModelChanged(
                    $order,
                    'history.send_bol_failed',
                    [
                        'load_id' => $order->load_id,
                        'email' => '',
                    ]
                )
            );

            Log::error($e->getTraceAsString());
        }
    }
}
