<?php

namespace App\Services\Orders\Parts;

use App\Dto\Orders\BS\SendDocsDto;
use App\Exceptions\Contact\SenderDoesNotHaveEmail;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Traits\TemplateToPdf;
use App\Models\Orders\Parts\Item;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Payment;
use App\Models\Settings\Settings;
use App\Models\Users\User;
use App\Notifications\Orders\Parts\SendDocs;
use App\Repositories\Settings\SettingRepository;
use App\Services\Events\EventService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class InvoiceService
{
    use TemplateToPdf;

    public function __construct(
        protected SettingRepository $settingRepository
    )
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

        $data = $this->getDataForPdf($order, $invoiceDate);

        return $this->template2pdf(
            [
                'pdf.order.parts.invoice',
                'pdf.order.parts.invoice-terms'
            ],
            $data,
            $stream,
            'invoice.pdf'
        );
    }

    public function getDataForPdf(Order $order, $date): array
    {
        $order->load(['items.inventory.brand']);
        $settings = $this->settingRepository->getInfo();

        $items = [];
        foreach ($order->items as $k => $item) {
            /** @var $item Item */
            $items[$k] = [
                'stock_number' => $item->inventory->stock_number,
                'name' => $item->inventory->brand?->name . ' ' . $item->inventory->name,
                'price' => $item->getPrice(),
                'qty' => $item->qty,
                'total' => $item->total(),
            ];
        }

        if($order->customer) {
            $customer = [
                'name' => $order->customer->full_name,
                'phone' => $order->customer->phone?->getValue(),
                'email' => $order->customer->email?->getValue(),
            ];
        } else {
            $customer = [
                'name' => null,
                'phone' => null,
                'email' => null,
            ];
            if($order->ecommerce_client) {
                $customer['name'] = $order->ecommerce_client->getFullNameAttribute();
                $customer['email'] = $order->ecommerce_client->email?->getValue();
            }
        }

        $deliveryMethod = $order->deliveries->isNotEmpty()
            ? $order->deliveries[0]->method->toUpperCase() . ', Free'
            : 'Free'
        ;

        if($order->payments->isNotEmpty()){
            $paymentMethod = null;
            $order->payments->each(function(Payment $item) use (&$paymentMethod) {
                $paymentMethod .= $item->payment_method->label() . ',';
            });
            $paymentMethod = substr($paymentMethod, 0, -1);
        } else {
            $paymentMethod = $order->payment_method?->label();
        }
        $totalOnlyItems = $order->getTotalOnlyItems();
        return [
            'logo' => isset($settings[Settings::ECOMM_LOGO_FIELD])
                ? $settings[Settings::ECOMM_LOGO_FIELD]->getFirstMedia(Settings::ECOMM_LOGO_FIELD)->getFullUrl() ?? null
                : null,
            'state' => isset($settings['ecommerce_state_id']->value)
                ? State::find($settings['ecommerce_state_id']->value)?->name
                : null,
            'settings' => [
                'company_name' => $settings['ecommerce_company_name']?->value ?? null,
                'address' => $settings['ecommerce_address']?->value ?? null,
                'city' => $settings['ecommerce_city']?->value ?? null,
                'zip' => $settings['ecommerce_zip']?->value ?? null,
                'email' => $settings['ecommerce_email']?->value ?? null,
                'phone' => $settings['ecommerce_phone']?->value ?? null,
                'phone_name' => $settings['ecommerce_phone_name']?->value ?? null,
                'payment_details' => $settings['ecommerce_billing_payment_details']?->value ?? null,
                'payment_options' => $settings['ecommerce_billing_payment_options']?->value ?? null,
                'terms_and_conditions' => $settings['ecommerce_billing_terms']->value ?? null,
            ],
            'order' => [
                'is_pickup' => $order->delivery_type?->isPickup(),
                'number' => $order->order_number,
                'date' => $date,
                'billing_address' => $order->billing_address,
                'delivery_address' => $order->delivery_address,
                'is_paid' => $order->is_paid,
                'paid_at' => $order->paid_at,
                'payment' => [
                    'method' => $paymentMethod,
                    'terms' => $order->payment_terms?->value,
                ],
                'items' => $items,
                'inventory_amount' => $totalOnlyItems,
                'tax_amount' => $order->getTax(),
                'total_amount' => $order->getAmount(),
                'subtotal_amount' => $order->getSubtotal(),
                'save_amount' => $order->getSavingAmount(),
                'delivery_method' => $deliveryMethod,
                'delivery_amount' => $order->getTotalDelivery($totalOnlyItems) - $order->delivery_cost,
                'custom_delivery_cost' => $order->delivery_cost,
                'customer' => $customer,
            ]
        ];
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

//        $order->markAsBilled();
//
        EventService::partsOrder($order)
            ->initiator($sender)
            ->setHistory([
                'receivers' => $sendDocsDto->emails()
            ])
            ->custom(OrderPartsHistoryService::ACTION_SEND_DOCS)
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
