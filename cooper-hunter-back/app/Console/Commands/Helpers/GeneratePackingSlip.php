<?php

namespace App\Console\Commands\Helpers;

use App\Dto\Orders\Dealer\OrderPackingSlipsOnecDto;
use App\Models\Orders\Dealer\Order;
use App\Services\Orders\Dealer\PackingSlipService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GeneratePackingSlip extends Command
{
    protected $signature = 'helpers:generate-ps';

    protected $description = 'Генерирует packing slip для order';

    protected $faker;

    public function __construct(
        protected PackingSlipService $service
    )
    {
        parent::__construct();

        $this->faker = \Faker\Factory::create();
    }

    public function handle(): int
    {
        $id = $this->ask('Enter Order ID');
        $order = Order::find($id);
        if($order === null){
            $this->warn("нет заказа [id - {$id}");
            return self::INVALID;
        }
        if($order === null){
            $this->warn("нет заказа [id - {$id}");
            return self::INVALID;
        }
        if($order->packingSlips->count() != 0){
            $this->warn("у заказа уже есть packingSlip");
            return self::INVALID;
        }

        $data = [];
        if ($order->items->count() >= 6 ){
            for ($i = 0; $i < 3; $i++ ){
                $data[$i] = [
                    'guid' => $this->faker->uuid,
                    'number' => $this->faker->postcode,
                    'tracking_number' => $this->faker->creditCardNumber,
                    'tracking_company' => $this->faker->company,
                    'shipped_date' => CarbonImmutable::now()->format('Y-m-d'),
                    'invoice' => $this->faker->creditCardNumber,
                    'invoice_date' => CarbonImmutable::now()->format('Y-m-d'),
                    'tax' => $this->faker->randomFloat(2, 2, 10),
                    'shipping_price' => $this->faker->randomFloat(2, 2, 1000),
                    'total' => $this->faker->randomFloat(2, 2, 1000),
                    'total_discount' => $this->faker->randomFloat(2, 2, 1000),
                    'total_with_discount' => $this->faker->randomFloat(2, 2, 1000),
                    'dimensions' => [
                        'pallet' => $this->faker->randomNumber(1,10),
                        'box_qty' => $this->faker->randomNumber(1,100),
                        'type' => 'box',
                        'weight' => $this->faker->randomFloat(2, 1, 200),
                        'width' => $this->faker->randomFloat(2, 1, 200),
                        'depth' => $this->faker->randomFloat(2, 1, 200),
                        'height' => $this->faker->randomFloat(2, 1, 200),
                        'class_freight' => $this->faker->randomNumber(1,10),
                    ]
                ];
                foreach ($order->items->pop(2) as $k => $item){
                    $data[$i]['products'][$k] = [
                        'guid' => $item->product->guid,
                        'discount' => $this->faker->randomFloat(2, 1, 200),
                        'qty' => $this->faker->randomNumber(2, 10),
                        'total' => $this->faker->randomFloat(2, 1, 200),
                        'price' => $this->faker->randomFloat(2, 1, 200),
                        'description' => $this->faker->sentence,
                    ];
                }
            }

        } else {
            $count = $order->items->count();
//            dd($order->items);
            for ($i = 0; $i < $count; $i++ ){
                $data[$i] = [
                    'guid' => $this->faker->uuid,
                    'number' => $this->faker->postcode,
                    'tracking_number' => $this->faker->creditCardNumber,
                    'tracking_company' => $this->faker->company,
                    'shipped_date' => CarbonImmutable::now()->format('Y-m-d'),
                    'invoice' => $this->faker->creditCardNumber,
                    'invoice_date' => CarbonImmutable::now()->format('Y-m-d'),
                    'tax' => $this->faker->randomFloat(2, 2, 10),
                    'shipping_price' => $this->faker->randomFloat(2, 2, 1000),
                    'total' => $this->faker->randomFloat(2, 2, 1000),
                    'total_discount' => $this->faker->randomFloat(2, 2, 1000),
                    'total_with_discount' => $this->faker->randomFloat(2, 2, 1000),
                    'dimensions' => [
                        'pallet' => $this->faker->randomNumber(1,10),
                        'box_qty' => $this->faker->randomNumber(1,100),
                        'type' => 'box',
                        'weight' => $this->faker->randomFloat(2, 1, 200),
                        'width' => $this->faker->randomFloat(2, 1, 200),
                        'depth' => $this->faker->randomFloat(2, 1, 200),
                        'height' => $this->faker->randomFloat(2, 1, 200),
                        'class_freight' => $this->faker->randomNumber(1,10),
                    ]
                ];

                $product = $order->items->pop()->product;
                if(null == $product->guid){
                    $guid = $this->faker->uuid;
                    $product->update(['guid' => $guid]);
                    $product->refresh();
                }

                $data[$i]['products'][0] = [
                    'guid' => $product->guid,
                    'discount' => $this->faker->randomFloat(2, 1, 200),
                    'qty' => $this->faker->randomNumber(2, 10),
                    'total' => $this->faker->randomFloat(2, 1, 200),
                    'price' => $this->faker->randomFloat(2, 1, 200),
                    'description' => $this->faker->sentence,
                ];
            }
        }

        $this->service->addOrUpdatePackingSlips(
            $order,
            OrderPackingSlipsOnecDto::byArgs($data)
        );

        return self::SUCCESS;
    }
}
