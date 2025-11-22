<?php

namespace App\Listeners\Orders\Dealer;

use App\Events\Orders\Dealer\CheckoutOrderEvent;
use App\Models\Catalog\Categories\Category;
use App\Services\Orders\Dealer\OrderService;
use Core\Exceptions\TranslatedException;
use Illuminate\Contracts\Queue\ShouldQueue;

/*
 * - отправляем email менеджеру, привязаного к компании
 * - если в заказе есть товары из категории (и ее дочерних категорий) - commercial, то отправляем
 *      email и менеджеру по комершил
 * - если в заказе только товары из commercial, то письмо отправляем только менеджеру по комершил
 */
class SendEmailToCompanyManagerListener implements ShouldQueue
{
    public function handle(CheckoutOrderEvent $event): void
    {
        $order = $event->getOrder()->load(['items.product']);
        try {
            $commercialCategoryIds = $this->getCategoryIds();
            $count = 0;

            foreach ($order->items as $item) {
                if(in_array($item->product->category_id, $commercialCategoryIds)){
                    $count++;
                }
            }

            /** @var $service OrderService */
            $service = resolve(OrderService::class);

            logger_info("SEND EMAIL LISTENER");

            if($count > 0){
                $service->sendEmailToCommercialManager($order);
            }
            if($count != $order->items->count()) {
                $service->sendEmailToManager($order);
            }

        } catch (\Throwable $e){
            throw new TranslatedException($e->getMessage(), 502);
        }
    }

    private function getCategoryIds(): array
    {
        $ids = [];
        $category = Category::query()
            ->with(['children'])
            ->commercial()
            ->first();

        if($category){
            $ids = $this->setCategoryChildrenId($ids, $category->children);
            $ids[] = $category->id;
        }

        return $ids;
    }

    private function setCategoryChildrenId(array &$item, $categories): array
    {
        foreach ($categories as $category){
            $item[] = $category->id;
            if($category->children->isNotEmpty()){
                $this->setCategoryChildrenId($item, $category->children);
            }
        }

        return $item;
    }
}
