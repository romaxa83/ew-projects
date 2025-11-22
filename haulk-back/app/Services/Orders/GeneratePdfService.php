<?php

namespace App\Services\Orders;

use App\Dto\Orders\SendDocsDto;
use App\Events\Orders\OrderUpdateEvent;
use App\Exceptions\Contact\RecipientDoesNotHaveEmail;
use App\Exceptions\Contact\SenderDoesNotHaveEmail;
use App\Exceptions\Order\EmptyInvoiceTotalDue;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use App\Models\Saas\Company\Company;
use App\Models\SendDocsDelay;
use App\Models\Users\User;
use App\Notifications\Orders\SendDocs;
use App\Notifications\SendPdfBol;
use App\Services\Events\EventService;
use App\Services\Logs\DeliveryLogService;
use App\Traits\TemplateToPdf;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GeneratePdfService
{
    use TemplateToPdf;

    public function getAvailableInvoices(Order $order): ?array
    {
        $brokerTotal = OrderPaymentService::init()->getTotalPaymentForecast($order, Payment::PAYER_BROKER)['total'];
        $customerTotal = OrderPaymentService::init()->getTotalPaymentForecast($order, Payment::PAYER_CUSTOMER)['total'];
        $result = [];

        if (!empty($brokerTotal)) {
            $result[] = [
                'amount' => $brokerTotal,
                'recipient' => Payment::PAYER_BROKER
            ];
        }

        if (!empty($customerTotal)) {
            $result[] = [
                'amount' => $customerTotal,
                'recipient' => Payment::PAYER_CUSTOMER
            ];
        }

        return !empty($result) ? $result : null;
    }

    /**
     * @param Carbon $invoiceDate
     * @param Order $order
     * @param string $invoiceRecipient
     * @param bool $stream
     * @param string $filename
     * @return string|null
     * @throws EmptyInvoiceTotalDue
     * @throws Throwable
     */
    private function getInvoicePdf(
        Carbon $invoiceDate,
        Order $order,
        string $invoiceRecipient,
        bool $stream = false,
        string $filename = 'invoice.pdf'
    ): ?string {

        $company = $order->user->getCompany();

        $profile = $company->getProfileData();

        $payment = $order->payment;

        [$expenses, $bonuses, $total] = array_values(OrderPaymentService::init()->getTotalPaymentForecast($order, $invoiceRecipient));

        if (empty($total)) {
            throw new EmptyInvoiceTotalDue($invoiceRecipient);
        }

        return $this->template2pdf(
            'pdf.invoice',
            [
                'load_id' => $order->load_id,
                'invoice_id' => $invoiceRecipient === Payment::PAYER_BROKER ?
                    $payment->broker_payment_invoice_id :
                    $payment->customer_payment_invoice_id,
                'invoice_date' => $invoiceDate->format(config('formats.pdf_date')),
                'terms' => $order->payment->terms,
                'profile' => [
                    'name' => $profile->name,
                    'address' => $profile->address,
                    'location' => $profile->city . ', ' .
                        (!empty($profile->state) ? $profile->state->name . ' ' : '') .
                        $profile->zip,
                    'mc_number' => $profile->mc_number,
                    'email' => $profile->email,
                    'phone' => $profile->phone,
                    'phones' => $profile->phones
                ],
                'bill_to' => $invoiceRecipient === Payment::PAYER_BROKER ?
                    $order->getShipperContact() : (
                    $payment->customer_payment_location === Order::LOCATION_PICKUP ?
                        $order->getPickupContact() :
                        $order->getDeliveryContact()
                    ),
                'origin' => $order->getPickupContact(),
                'destination' => $order->getDeliveryContact(),
                'vehicles' => $order->vehicles()
                    ->get()
                    ->map(
                        function (Vehicle $item) {
                            return [
                                'year' => $item->year,
                                'make' => $item->make,
                                'model' => $item->model,
                                'vin' => $item->vin,
                                'type' => $item->type_name,
                                'color' => $item->color
                            ];
                        }
                    )
                    ->toArray(),
                'expenses' => $expenses->toArray(),
                'bonuses' => $bonuses->toArray(),
                'total' => number_format($total, config('orders.invoice.price_decimal')),
                'is_paid' => isset($order->payment->paidFlags()->paidAt),
                'billing_details' => $company->getBillingPaymentDetails() ?
                    [
                        'phone' => ($profile->billing_phone_name ?? '') . ' ' . ($profile->billing_phone ?? ''),
                        'email' => $profile->billing_email,
                        'phones' => !empty(
                        $company->getBillingContacts()
                        ) ? ' (' . $company->getBillingContactsAsString() . ')' : '',
                        'details' => $company->getBillingPaymentDetails()
                    ] : null,
                'terms_conditions' => $company->getTermsAndConditions(),
            ],
            $stream,
            $filename
        );
    }

    /**
     * @param Order $order
     * @param array $invoice
     * @throws EmptyInvoiceTotalDue
     * @throws Throwable
     */
    public function getInvoice(Order $order, array $invoice): void
    {
        $order->payment->{$invoice['recipient'] . '_payment_invoice_id'} = empty($invoice['id']) ? $order->load_id : $invoice['id'];

        $this->getInvoicePdf(
            !empty($invoice['date']) ? $invoice['date'] : Carbon::now(),
            $order,
            $invoice['recipient'],
            true
        );
    }

    private function setInvoicePaymentData(Order $order, string $invoiceId, string $invoiceRecipient, User $sender)
    {
        $order->payment->{$invoiceRecipient . '_payment_invoice_id'} = $invoiceId;
        $order->payment->{$invoiceRecipient . '_payment_invoice_issue_date'} = $order->payment->{$invoiceRecipient . '_payment_invoice_issue_date'} ?? Carbon::now()->timestamp;

        $order->payment->save();

        $order->is_billed = true;
        $order->save();

        OrderPaymentService::init()->updatePlannedDate($order->payment);

        EventService::order($order)
            ->user($sender)
            ->update()
            ->broadcast();
    }

    /**
     * @param SendDocsDto $sendDocsDto
     * @param object $profile
     * @param Order $order
     * @param bool $showShipperInfo
     * @param array|null $invoice
     * @return SendDocs
     * @throws EmptyInvoiceTotalDue
     * @throws FileNotFoundException
     * @throws Throwable
     */
    protected function generateNotification(
        SendDocsDto $sendDocsDto,
        object $profile,
        Order $order,
        bool $showShipperInfo,
        ?array $invoice,
        User $sender
    ): SendDocs {
        $attachments = [];

        if ($sendDocsDto->isSendBol()) {
            $attachments[SendDocs::ATTACHMENT_BOL] = [
                'data' => $this->getBolPdf($profile, $order, $showShipperInfo),
                'name' => 'bol.pdf'
            ];
        }

        if ($sendDocsDto->isSendInvoice()) {
            $this->setInvoicePaymentData($order, $invoice['id'], $invoice['recipient'], $sender);

            event(new OrderUpdateEvent($order));

            $attachments[$invoice['recipient'] === Payment::PAYER_CUSTOMER ? SendDocs::ATTACHMENT_CUSTOMER_INVOICE : SendDocs::ATTACHMENT_BROKER_INVOICE] = [
                'data' => $this->getInvoicePdf(
                    $invoice['date'],
                    $order,
                    $invoice['recipient']
                ),
                'name' => 'invoice.pdf'
            ];
        }

        if ($sendDocsDto->isSendW9()) {
            $attachments[SendDocs::ATTACHMENT_W9] = [
                'data' => $this->template2pdf(
                    'pdf.w9',
                    [
                        'w9' => base64_encode(
                            Storage::get($sendDocsDto->w9()->getPath())
                        ),
                        'mime' => $sendDocsDto->w9()->mime_type
                    ],
                    false,
                    'w9.pdf'
                ),
                'name' => 'w9.pdf'
            ];
        }

        return new SendDocs($order, $attachments);
    }

    /**
     * @param User $sender
     * @param SendDocsDto $sendDocsDto
     * @throws Throwable
     * @throws EmptyInvoiceTotalDue
     * @throws SenderDoesNotHaveEmail
     */
    public function sendDocs(User $sender, SendDocsDto $sendDocsDto, bool $auto = false): void
    {
        $company = $sender->getCompany();

        $profile = $company->getProfileData();

        if (!$profile->email) {
            throw new SenderDoesNotHaveEmail;
        }

        $deliveryLogService = resolve(DeliveryLogService::class);


        foreach ($sendDocsDto->orders() as $order) {
            $invoice = $order['invoice'];

            $showShipperInfo = $order['show_shipper_info'];

            $order = Order::find($order['id']);

            $notification = $this->generateNotification($sendDocsDto, $profile, $order, $showShipperInfo, $invoice, $sender);

            $deliveryLogService->createDocumentDescription($notification->attachments());

            if ($sendDocsDto->isSendToEmail()) {
                foreach ($sendDocsDto->emails() as $email) {
                    Notification::route('mail', $email)->notify($notification);
                }

                $deliveryLogService->logSentDocsViaEmail($order, $sender, $sendDocsDto->emails(), $auto);
            }

            if ($sendDocsDto->isSendToFax()) {
                Notification::route('fax', $sendDocsDto->fax())->notify($notification);

                $deliveryLogService->logSentDocsViaFax($order, $sender, $sendDocsDto->fax());
            }
        }
    }

    /**
     * @param Order $order
     * @param array $notification_settings
     * @throws Throwable
     */
    public function sendInvoiceAutomatic(Order $order): void
    {
        $deliveryLogService = resolve(DeliveryLogService::class);

        //Try to send broker Invoice
        try {
            if (!isset($order->shipper_contact['email'])) {
                throw new RecipientDoesNotHaveEmail;
            }

            $this->sendDocs($order->user, SendDocsDto::create()->autoInvoice($order, Payment::PAYER_BROKER), true);
        } catch (EmptyInvoiceTotalDue $e) {

        } catch (SenderDoesNotHaveEmail|RecipientDoesNotHaveEmail $e) {
            $deliveryLogService->logFailAutomaticSendDocViaEmail($order, SendDocs::ATTACHMENT_BROKER_INVOICE, $e->getMessage());
        } catch (Exception $e) {

            Log::error($e);

            $deliveryLogService->logFailAutomaticSendDocViaEmail($order, SendDocs::ATTACHMENT_BROKER_INVOICE);

        }

        //Try to send customer invoice
        try {
            $location = $order->payment->customer_payment_location;

            if (!$location) {
                throw new EmptyInvoiceTotalDue(Payment::PAYER_CUSTOMER);
            }

            if (!isset($order->{$location . '_contact'}['email'])) {
                throw new RecipientDoesNotHaveEmail;
            }

            $this->sendDocs($order->user, SendDocsDto::create()->autoInvoice($order, Payment::PAYER_CUSTOMER), true);
        } catch (EmptyInvoiceTotalDue $e) {

        } catch (SenderDoesNotHaveEmail|RecipientDoesNotHaveEmail $e) {
            $deliveryLogService->logFailAutomaticSendDocViaEmail($order, SendDocs::ATTACHMENT_CUSTOMER_INVOICE, $e->getMessage());
        } catch (Exception $e) {

            Log::error($e);

            $deliveryLogService->logFailAutomaticSendDocViaEmail($order, SendDocs::ATTACHMENT_CUSTOMER_INVOICE);

        }
    }

    public function sendDocsDelayed(Order $order, string $inspectionType): void
    {
        $tasks = $order->sendDocsDelayed()
            ->where('inspection_type', $inspectionType)
            ->get();

        $tasks->each(
            function (SendDocsDelay $task) use ($order) {
                $requestData = $task->request_data;

                $this->sendDocs(
                    User::find($task->sender_id),
                    SendDocsDto::create()->mobileOrigin($requestData, $order)
                );

                $task->delete();
            }
        );
    }

    private function getOrderCompany(Order $order): Company
    {
        return $order->user->getCompany();
    }

    /**
     * @param Order $order
     * @param bool $showShipperInfo
     * @throws Throwable
     */
    public function getBol(Order $order, bool $showShipperInfo = false): void
    {
        $this->getBolPdf(
            $this->getOrderCompany($order)->getProfileData(),
            $order,
            $showShipperInfo,
            true
        );
    }

    /**
     * @param $profileData
     * @param Order $order
     * @param bool $show_shipper_info
     * @param bool $stream
     * @param string $filename
     * @return string|null
     * @throws Throwable
     */
    private function getBolPdf(
        $profileData,
        Order $order,
        bool $show_shipper_info = false,
        bool $stream = false,
        string $filename = 'bol.pdf'
    ): ?string {
        return $this->template2pdf(
            'pdf.bol',
            [
                'profile' => $profileData,
                'order' => $order,
                'driver' => $order->driver,
                'driver_pickup' => $order->driverPickup,
                'driver_delivery' => $order->driverDelivery,
                'add_pickup_delivery_dates_to_bol' => $this->getOrderCompany($order)->ifAddPickupDeliveryDatesToBol(),
                'terms_conditions' => $this->getOrderCompany($order)->getTermsAndConditions(),
                'show_shipper_info' => $show_shipper_info,
            ],
            $stream,
            $filename
        );
    }

    /**
     * @param Order $order
     * @param array $notification_settings
     * @throws Throwable
     */
    public function sendBolAutomatic(Order $order, array $notification_settings): void
    {
        if (!isset($order->shipper_contact['email'])) {
            throw new Exception(trans('No recipient email provided.'));
        }

        $profileData = $this->getOrderCompany($order)->getProfileData();

        if (!$profileData->email) {
            throw new Exception(trans('No sender email provided.'));
        }

        $pdf = $this->getBolPdf($profileData, $order);

        $recipients = [$order->shipper_contact['email']];

        if (is_array($notification_settings['receive_bol_copy_emails'])) {
            foreach ($notification_settings['receive_bol_copy_emails'] as $el) {
                $recipients[] = $el['value'];
            }
        }

        $notification = new SendPdfBol($order, $pdf);

        foreach ($recipients as $email) {
            Notification::route('mail', $email)->notify($notification);
        }
    }

    /**
     * @param Order $order
     * @throws Throwable
     */
    public function printOrder(Order $order): void
    {
        $this->template2pdf(
            'pdf.order',
            [
                'profile' => $this->getOrderCompany($order)->getProfileData(),
                'order' => $order,
            ],
            true,
            'invoice.pdf'
        );
    }
}
