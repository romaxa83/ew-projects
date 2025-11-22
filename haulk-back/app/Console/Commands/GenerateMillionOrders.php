<?php

namespace App\Console\Commands;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use App\Models\Users\User;
use Artisan;
use DB;
use Illuminate\Console\Command;

class GenerateMillionOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:generate-millions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $orderFactory;
    private $userFactory;
    private $paymentFactory;
    private $vehicleFactory;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Millions of fake orders will be generated now!');

        if (!in_array($this->ask('Database will be emptied. Are you sure you want this? [y/n]: '), ['y', 'Y'])) {
            $this->info('Canceled');
            return 0;
        }

        $this->info(now()->toIso8601String());

        $this->info('Refreshing database..');
        Artisan::call('migrate:fresh');

        $this->info('Run seeds..');
        Artisan::call('db:seed');

        $batchSize = 10; // 1000
        $iterationsCount = 10; // 1000
        $carriersCount = 10; // 100

        $this->orderFactory = factory(Order::class, $batchSize);
        $this->paymentFactory = factory(Payment::class, $batchSize);
        $this->vehicleFactory = factory(Vehicle::class, $batchSize);
        $this->userFactory = factory(User::class);

        foreach (range(1, $carriersCount) as $carrier_id) {
            foreach (range($carrier_id * $carriersCount + 1, $carrier_id * $carriersCount + $iterationsCount) as $iteration) {
                $this->info('Iteration ' . $iteration);

                $orders = $this->getOrders($iteration, $batchSize, $carrier_id);
                DB::table('orders')->insert($orders->all());

                $payments = $this->getPayments($iteration, $batchSize);
                DB::table('payments')->insert($payments->all());

                $vehicles = $this->getVehicles($iteration, $batchSize);
                DB::table('vehicles')->insert($vehicles->all());

                $this->info(' - done, ' . $batchSize . ' orders inserted.');
            }
        }

        $this->info(now()->toIso8601String());

        $this->info('All done!');

        return 0;
    }

    private function getOrders($iteration, $batchSize, $carrier_id)
    {
        $dispatcher = $this->userFactory->create(
            [
                'carrier_id' => $carrier_id,
            ]
        );
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $driver = $this->userFactory->create(
            [
                'carrier_id' => $carrier_id,
                'owner_id' => $dispatcher->id,
            ]
        );
        $driver->assignRole(User::DRIVER_ROLE);

        $orders = $this->orderFactory->make(
            [
                'carrier_id' => $carrier_id,
                'user_id' => $dispatcher->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $orders->transform(
            function ($item, $i) use ($iteration, $batchSize, $driver) {
                if ($i > floor($batchSize * 0.15)) {
                    $item->driver_id = $driver->id;
                }

                $item->setContactNameFields();
                $item->setCalculatedStatusField();

                $data = $item->toArray();

                $data['id'] = $iteration * $batchSize + $i;

                $data['pickup_contact'] = json_encode($data['pickup_contact']);
                $data['delivery_contact'] = json_encode($data['delivery_contact']);
                $data['shipper_contact'] = json_encode($data['shipper_contact']);

                return $data;
            }
        );

        return $orders;
    }

    private function getPayments($iteration, $batchSize)
    {
        $payments = $this->paymentFactory->make(
            [
                'order_id' => null,
            ]
        );

        $payments->transform(
            function ($item, $i) use ($iteration, $batchSize) {
                $data = $item->toArray();

                $data['id'] = $iteration * $batchSize + $i;
                $data['order_id'] = $iteration * $batchSize + $i;

                return $data;
            }
        );

        return $payments;
    }

    private function getVehicles($iteration, $batchSize)
    {
        $vehicles = $this->vehicleFactory->make(
            [
                'order_id' => null,
            ]
        );

        $vehicles->transform(
            function ($item, $i) use ($iteration, $batchSize) {
                $data = $item->toArray();

                $data['id'] = $iteration * $batchSize + $i;
                $data['order_id'] = $iteration * $batchSize + $i;

                return $data;
            }
        );

        return $vehicles;
    }
}
