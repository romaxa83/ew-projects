<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Orders\Order;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;

class OrderMobileHistoryTest extends OrderTestCase
{
    use OrderFactoryHelper;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    /**
     * @param $sort
     * @param $orderSign
     * @dataProvider itShowOrdersHistoryStatusInCorrectSortDirectionDataProvider
     */
    public function test_it_show_orders_history_status_in_correct_sort_direction($sort, $orderSign): void
    {
        $this->loginAsCarrierDriver();

        $this->generateFakeOrdersWithMobileHistoryStatus($this->authenticatedUser);
        $this->makeDocuments();

        $response = $this->getJson(
            route(
                'order-mobile.index',
                [
                    'order_by' => $sort,
                    'status' => Order::MOBILE_TAB_HISTORY,
                ]
            ),
        )
            ->assertOk();

        $orders = $response->json('data');

        $orders = array_map(
            function (array $order) {
                return $order['delivery_date_actual'];
            },
            $orders
        );

        $first = array_shift($orders);
        $second = array_shift($orders);
        $third = array_shift($orders);
        $fourth = array_shift($orders);

        if ($orderSign === '>') {
            $this->assertTrue($first > $second);
            $this->assertTrue($second > $third);
            $this->assertTrue($third > $fourth);
        } else {
            $this->assertTrue($first < $second);
            $this->assertTrue($second < $third);
            $this->assertTrue($third < $fourth);
        }
    }

    public function itShowOrdersHistoryStatusInCorrectSortDirectionDataProvider(): array
    {
        return [
            ['desc', '>'],
            [null, '>'],
            ['asc', '<'],
        ];
    }
}
