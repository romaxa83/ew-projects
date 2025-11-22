<?php

namespace App\Services\Orders\BS;

use App\Dto\Orders\BS\SendDocsDto;
use App\Exceptions\Contact\SenderDoesNotHaveEmail;
use App\Foundations\Modules\History\Services\OrderBSHistoryService;
use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Traits\TemplateToPdf;
use App\Models\Orders\BS\Order;
use App\Models\Settings\Settings;
use App\Models\Users\User;
use App\Notifications\Orders\BS\SendDocs;
use App\Repositories\Settings\SettingRepository;
use App\Services\Events\EventService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;


class InvoiceService
{
    use TemplateToPdf;

    public function __construct(protected SettingRepository $settingRepository)
    {}

    public function generateInvoicePdf(
        Order $order,
        Carbon $invoiceDate = null,
        bool $stream = false
    ): ?string
    {
        if (!$invoiceDate) {
            $invoiceDate = now();
        }

        $settings = $this->settingRepository->getInfo();

        $inventoryAmount = round($order->getInventoryAmount(), 2);
        $inventoryDiscountAmount = round($inventoryAmount * $order->discount / 100 ,2);
        $inventoryTaxAmount = round(($inventoryAmount - $inventoryDiscountAmount) * $order->tax_inventory / 100, 2);
        $inventoryAmountAfterDiscountAndTax = round($inventoryAmount - $inventoryDiscountAmount + $inventoryTaxAmount, 2);

        $laborAmount = round($order->getLaborAmount(), 2);
        $laborDiscountAmount = round($laborAmount * $order->discount / 100, 2);
        $laborTaxAmount = round(($laborAmount - $laborDiscountAmount) * $order->tax_labor / 100, 2);
        $laborAmountAfterDiscountAndTax = round($laborAmount - $laborDiscountAmount + $laborTaxAmount, 2);

        $totalAmount = $inventoryAmountAfterDiscountAndTax + $laborAmountAfterDiscountAndTax;

        $logo = isset($settings['logo'])
            ? $settings['logo']->getFirstMedia(Settings::LOGO_FIELD)->getFullUrl() ?? null
            : null;

        return $this->template2pdf(
            'pdf.order.bs.invoice',
            [
                'logo' => $logo,
                'state' => isset($settings['state_id']->value) ? State::find($settings['state_id']->value)?->name : '',
                'settings'  => $settings,
                'orderNumber' => $order->order_number,
                'invoiceNumber' => $order->order_number,
                'invoiceDate' => $invoiceDate->setTimezone($settings['timezone']->value ?? 'UTC')->format('M j, Y'),
                'dueDate' => $order->due_date->format('M j, Y'),
                'customer' => [
                    'name' => $order->vehicle->customer
                        ? $order->vehicle->customer->full_name
                        : null,
                    'phone' => $order->vehicle->customer
                        ? $order->vehicle->customer->phone
                        : null,
                    'email' => $order->vehicle->customer
                        ? $order->vehicle->customer->email
                        : null,
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

    public function sendDocs(
        User $sender,
        Order $order,
        SendDocsDto $sendDocsDto
    ): void
    {
        $settings = $this->settingRepository->getInfo();

        if (!($settings['email']->value ?? null)) {
            throw new SenderDoesNotHaveEmail();
        }

        $notification = $this->generateNotification($sendDocsDto, $order);

        foreach ($sendDocsDto->emails() as $email) {
            Notification::route('mail', $email)->notify($notification);
        }

        $order->markAsBilled();

        EventService::bsOrder($order)
            ->initiator($sender)
            ->setHistory([
                'receivers' => $sendDocsDto->emails()
            ])
            ->custom(OrderBSHistoryService::ACTION_SEND_DOCS)
            ->exec()
        ;

    }

    protected function generateNotification(
        SendDocsDto $sendDocsDto,
        Order $order
    ): SendDocs
    {
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

