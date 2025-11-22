<?php

namespace App\Services\BodyShop\Orders;

use App\Dto\BodyShop\Orders\SendDocsDto;
use App\Exceptions\Contact\SenderDoesNotHaveEmail;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Settings\Settings;
use App\Models\Locations\State;
use App\Models\Users\User;
use App\Notifications\BodyShop\Orders\SendDocs;
use App\Services\BodyShop\Settings\SettingsService;
use App\Services\Events\EventService;
use App\Traits\TemplateToPdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;


class InvoiceService
{
    use TemplateToPdf;

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function generateInvoicePdf(Order $order, Carbon $invoiceDate = null, bool $stream = false): ?string
    {
        if (!$invoiceDate) {
            $invoiceDate = now();
        }

        $settings = $this->settingsService->getInfo();

        $inventoryAmount = round($order->getInventoryAmount(), 2);
        $inventoryDiscountAmount = round($inventoryAmount * $order->discount / 100 ,2);
        $inventoryTaxAmount = round(($inventoryAmount - $inventoryDiscountAmount) * $order->tax_inventory / 100, 2);
        $inventoryAmountAfterDiscountAndTax = round($inventoryAmount - $inventoryDiscountAmount + $inventoryTaxAmount, 2);

        $laborAmount = round($order->getLaborAmount(), 2);
        $laborDiscountAmount = round($laborAmount * $order->discount / 100, 2);
        $laborTaxAmount = round(($laborAmount - $laborDiscountAmount) * $order->tax_labor / 100, 2);
        $laborAmountAfterDiscountAndTax = round($laborAmount - $laborDiscountAmount + $laborTaxAmount, 2);

        $totalAmount = $inventoryAmountAfterDiscountAndTax + $laborAmountAfterDiscountAndTax;
//        $totalAmount = $order->getAmount();

        $logo = isset($settings['logo'])
            ? $settings['logo']->getFirstMedia(Settings::LOGO_FIELD)->getFullUrl() ?? null
            : null;

//        dd($order->vehicle);

        return $this->template2pdf(
            'bodyshop.pdf.invoice',
            [
                'logo' => $logo,
                'state' => isset($settings['state_id']->value) ? State::find($settings['state_id']->value)->name : '',
                'settings'  => $settings,
                'orderNumber' => $order->order_number,
                'invoiceNumber' => $order->order_number,
                'invoiceDate' => $invoiceDate->setTimezone($settings['timezone']->value ?? 'UTC')->format('M j, Y'),
                'dueDate' => $order->due_date->format('M j, Y'),
                'customer' => [
                    'name' => $order->vehicle->customer
                        ? $order->vehicle->customer->getFullName()
                        : $order->vehicle->owner->full_name,
                    'phone' => $order->vehicle->customer
                        ? $order->vehicle->customer->phone
                        : $order->vehicle->owner->phone,
                    'email' => $order->vehicle->customer
                        ? $order->vehicle->customer->email
                        : $order->vehicle->owner->email,
                    ],
                'order' => $order,
                'vehicle' => $order->vehicle,
                'inventoryAmount' => $inventoryAmount,
                'inventoryDiscountAmount' => $inventoryDiscountAmount,
                'inventoryTaxAmount' => $inventoryTaxAmount,
                'inventoryAmountAfterDiscountAndTax' => $inventoryAmountAfterDiscountAndTax,
                'laborAmount' => $laborAmount,
                'laborDiscountAmount' => $laborDiscountAmount,
                'laborTaxAmount' => $laborTaxAmount,
                'laborAmountAfterDiscountAndTax' => $laborAmountAfterDiscountAndTax,
                'totalAmount' => $totalAmount,
            ],
            $stream,
            'invoice.pdf'
        );
    }

    public function sendDocs(User $sender, Order $order, SendDocsDto $sendDocsDto): void
    {
        $settings = $this->settingsService->getInfo();

        if (!($settings['email']->value ?? null)) {
            throw new SenderDoesNotHaveEmail();
        }

        $notification = $this->generateNotification($sendDocsDto, $order);

        foreach ($sendDocsDto->emails() as $email) {
            Notification::route('mail', $email)->notify($notification);
        }

        $order->markAsBilled();

        EventService::bsOrder($order)
            ->user($sender)
            ->sendDocs($sendDocsDto->emails());
    }

    protected function generateNotification(
        SendDocsDto $sendDocsDto,
        Order $order
    ): SendDocs {
        $attachments = [];

        if ($sendDocsDto->isSendInvoice()) {
            $attachments[SendDocs::ATTACHMENT_CUSTOMER_INVOICE] = [
                'data' => $this->generateInvoicePdf(
                    $order,
                    $sendDocsDto->getInvoiceDate()
                ),
                'name' => 'invoice.pdf'
            ];
        }

        return new SendDocs($order, $attachments);
    }

}
