<?php

namespace WezomCms\AmoCrm\Jobs;

use AmoCRM\Helpers\EntityTypesInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use WezomCms\AmoCrm\Services\AmoCrmService;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderItem;


class AmoSubmitOrderToCrmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(protected int $orderId)
    {
        $this->onQueue(config('cms.amo-crm.amo-crm.queue'));
    }

    public function handle()
    {
        $order = Order::whereKey($this->orderId)->with(['items.product', 'client'])->first();

        if (!$order) {
            return;
        }

        $locale = config('cms.amo-crm.amo-crm.locale');

        /** @var AmoCrmService $service */
        $service = resolve(AmoCrmService::class);

        $leadName = __('cms-amo-crm::site.New order :id', ['id' => $order->id], $locale);

        $fields = array_filter(
            [
                'order_id' => (string)$order->id,
                //'paid' => (bool)($order->payed ?? false),
                'delivery_method' => $order->delivery->driver
            ]
        );

        $exists = $service->getContacts($order->client->phone);

        if ($contact = $exists->first()) {
            try {
                $contact->setName($order->client->full_name)->setFirstName('')->setLastName('');
                $contactId = $service->updateContact($contact);
            }catch (Exception $e){
                Log::channel('amo')->error('error update contact (' . $contact->getId() . ') amo send ' . $e->getMessage());
                $contactId = $contact->getId();
            }
        } else {
            $contactId = $service->addContact(
                $order->client->full_name,
                [
                    'phone' => $order->client->phone,
                    'email' => $order->client->email
                ]
            );
        }

        $leadId = $service->addLead($leadName, 'new_order', $order->whole_purchase_price, $fields);

        if (isset($order->items)) {
            $template = __('cms-amo-crm::site.New order template', ['id' => $order->id], $locale);

            $productsNote = [];

            /** @var OrderItem $item */
            foreach ($order->items as $key => $item) {
                $product = $item->product;

                $productName = $product->getTranslation($locale)->name;

                $productUrl = route('admin.products.edit', ['product' => $product->id]);

                $productsNote[] = 'Товар №' . ($key + 1) . ' - ' . $productName . " ($item->quantity)" . ' ' . $productUrl;
            }

            $deliveryInformation = '';

            $orderUrl = route('admin.orders.edit', $order->id);
            
            $recipientInformation = implode(
                    ', ',
                    [
                        $order->recipient->phone,
                        $order->recipient->full_name,
                    ]) ?? '';

            $service->addNoteToLead(
                $leadId,
                sprintf(
                    $template,
                    $order->id,
                    $orderUrl,
                    implode("\n", $productsNote),
                    'Способ оплаты: ' . optional(optional($order->payment)->getTranslation($locale))->name ?? '-',
                    'Способ доставки: ' . optional(optional($order->delivery)->getTranslation($locale))->name ?? '-',
                    implode("\n", [$deliveryInformation, $recipientInformation]) ?? '',
                    'Комментарий покупателя: ' . $order->comment ?? '-'
                )
            );
        }

        dispatch(new CreateLink('lead', $leadId, 'contact', $contactId));

        dispatch(new ContactByRequest($leadId,
            EntityTypesInterface::LEADS,
            __('cms-amo-crm::site.Contact to client', [], config('cms.amo-crm.amo-crm.locale'))));

        $order->amocrm_lead_id = $leadId;
        $order->save();
    }
}
