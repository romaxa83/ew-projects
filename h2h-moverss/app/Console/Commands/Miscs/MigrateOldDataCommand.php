<?php

namespace App\Console\Commands\Miscs;

use App\Http\Controllers\OrderCalculateController;
use App\Models\{Client, Client\Email, Client\Phone, Employee, Order, Truck\Truck};
use App\User;
use DB;
use Illuminate\Console\Command;
use Str;

ini_set('memory_limit', '800M');

class MigrateOldDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miscs:migrate-old-data {prefix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Притянуть старые заказы';
    /**
     * @var array
     */
    private $o_statuses;
    /**
     * @var array
     */
    private $o_sources;
    private $o_source2NewId;

    private $employee2newId = [];

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
        $prefix = $this->argument('prefix');

        if ($prefix === 'lamovers') {
            $branch_id = 2;
        } elseif ($prefix === 'h2hmovers') {
            $branch_id = 1;
            $this->syncEmployees($prefix, $branch_id);
            $this->syncTrucks($prefix, $branch_id);
        } else {
            exit('Incorrect prefix');
        }

        $this->miscsData($prefix);

        $order = Order::where('division_id', $branch_id)
            ->whereHas('extended', function ($q) {
                $q->orderBy('ext_id', 'desc');
            })
            ->first();


        $last_import_order = $order->extended->ext_id ?? 0;


        // Заказы
        $orders = DB::connection('mysqlImport')->table($prefix.'_orders')
            ->where('id', '>', $last_import_order)
            ->when($branch_id === 1, function ($q) {
                $q->where('created_at', '<', '2022-01-01');
            })
            ->latest()->get();

        foreach ($orders as $order) {
            'Import: '.$order->id.PHP_EOL;
            $emails = DB::connection('mysqlImport')
                ->table($prefix.'_orders_emails')->whereOrderId($order->id)->get()
                ->filter(function ($item) {
                    // Фильтруем корявые email
                    return filter_var($item->email, FILTER_VALIDATE_EMAIL);
                });
            $phones = DB::connection('mysqlImport')
                ->table($prefix.'_orders_phones')->whereOrderId($order->id)->get()
                ->filter(function ($item) {
                    // Фильтруем корявые номера
                    $phone = preg_replace('/[^0-9]/', '', $item->phone);
                    if ($phone === '8059537127') {
                        $phone = null;
                    }

                    return !empty($phone);
                });

            // Поиск и создание юзера
            $client_id = $this->clientCreateOrUpdate($order, $emails, $phones);

            $this->createOrder($order, $client_id, $branch_id);

            echo '.'.PHP_EOL;
        }

    }

    private function miscsData($prefix)
    {
        $this->o_statuses = DB::connection('mysqlImport')->table($prefix.'_orders_statuses')->get()
            ->pluck('status_name', 'id')->all();
        $this->o_sources = DB::connection('mysqlImport')->table($prefix.'_orders_sources')->get()
            ->pluck('source_name', 'id')->all();

        $sources = Order\Source::get(['id', 'title']);
        foreach ($this->o_sources as $k => $title) {
            $id = $sources->where('title', $title)->first()->id ?? null;
            if ($id) {
                $this->o_source2NewId[$k] = $id;
            }
        }
    }

    /**
     * Создать клиента или приаттачить ему номера тел и email.
     * @param $order
     * @param $emails
     * @param $phones
     * @return int|mixed
     */
    private function clientCreateOrUpdate($order, $emails, $phones)
    {
        $client_id = 0;

        // Найти по email
        if ($emails) {
            $in = $emails->map(function ($item) {
                return $item->email;
            })
                ->all();

            $find = Email::whereIn('value', $in)->first();
            if ($find) {
                echo 'Client found by emails '.print_r($in, 1).PHP_EOL;
                $client_id = $find->client_id;
            }
        }

        // По номеру тел.
        if (!$client_id && $phones) {
            $in = $phones->map(function ($item) {
                return $item->phone;
            })
                ->all();

            $find = Phone::whereIn('value', $in)->first();
            if ($find) {
                echo 'Client found by phone '.print_r($in, 1).PHP_EOL;
                $client_id = $find->client_id;
            }
        }

        if ($client_id) {
            $client = Client::find($client_id);
        } else {
            $client = new Client([
                'name' => $order->f_name,
                'lname' => $order->l_name,
            ]);
            $client->save();

            $client_id = $client->id;
            echo 'New Client created '.$client_id.PHP_EOL;
        }

        // Пробуем дообновить данные номеров тел. клиента и Email
        if ($emails) {
            foreach ($emails as $v) {
                $client->emails()
                    ->firstOrCreate([
                        'value' => $v->email
                    ]);
            }
        }

        if ($phones) {
            foreach ($phones as $v) {
                $client->phones()
                    ->firstOrCreate([
                        'value' => $v->phone,
                        'type_id' => 1
                    ]);
            }
        }

        return $client_id;
    }

    private function createOrder($data, $client_id, $division_id)
    {
        if ($division_id === 2) {
            $branch = 'lamovers';
        } elseif ($division_id === 1) {
            $branch = 'h2hmovers';
        }

        $type = 'local';
        if ($data->move_type === 'interstate') {
            $type = 'interstate';
        } elseif ($data->move_type === 'state') {
            $type = 'intrastate';
        }

        $notes[] = 'Old CRM: <a href="http://'.$branch.'.allymovers.com/order/show/'.$data->id.
            '" target="_blank">http://'.$branch.'.allymovers.com/order/show/'.$data->id.'</a>';
        $notes[] = 'Status: '.$this->o_statuses[$data->order_status] ?? '-';
        if ($data->order_source) {
            $notes[] = 'Source: '.$this->o_sources[$data->order_source] ?? '-';
        }

        // Find order
        $order = Order::where('division_id', $division_id)
            ->whereHas('extended', function ($q) use ($data) {
                $q->where('ext_id', $data->id);
            })
            ->first();
        if (!$order) {
            $order = new Order();
            $not_exists = true;
        }

        $order->client_id = $client_id;
        $order->division_id = $division_id;
        $order->source_id = $this->o_source2NewId[$data->order_source] ?? 0;
        $order->user_id = $this->employee2newId[$data->responsible_worker_id] ?? 0;
        $order->status_id = $this->h2hOrderStatus($data->order_status, $division_id);
        $order->created_at = $data->created_at;
        $order->updated_at = !empty($data->updated_at) ? $data->updated_at : now()->toDateTimeString();
        $order->hash = !empty($data->hash) ? $data->hash : Str::random(32);
//        $order->type = $type;
        $order->move_size_id = $data->move_size ?? null;
        $order->sizing_is_auto = (int) $data->weight_volume_auto;
        $order->sizing_volume = (float) $data->volume;
        $order->sizing_weight = (float) $data->weight;
        $order->save();

        $estimate = [
            'type' => $type,
            'trucks' => (int) $data->trucks,
            'crews' => (int) $data->crew,
            'is_locked' => 1,
            'calculated_moving_distance' => (float) $data->miles,
            'calculated_moving_distance_auto' => (float) $data->miles,
        ];

        if ($order->estimate()->exists()) {
            $order->estimate()->update($estimate);
        } else {
            $order->estimate()->create($estimate);
        }

        if (!Order\Estimate\Calculated::where('order_id', $order->id)->exists()) {
            $cCont = new OrderCalculateController($order);

            $total = $cCont->formatCurrency($data->total_price_min);
            if ($data->total_price_min !== $data->total_price_max) {
                $total = $cCont->formatCurrency($data->total_price_min).' - '.$cCont->formatCurrency($data->total_price_max);
            }

            Order\Estimate\Calculated::create([
                'order_id' => $order->id,
                'estimate_type' => $type,
                'title' => 'total',
                'value' => $total,
            ]);
        }

        // Первое создание заказа
        if (isset($not_exists)) {
            $order->extended()->updateOrCreate([
                'ext_id' => $data->id,
            ]);

            $note = new Order\Notes();
            $note->order_id = $order->id;
            $note->user_id = 0;
            $note->text = implode(PHP_EOL, $notes);
            $note->is_pinned = 1;
            $note->save();

            $prefix = $this->argument('prefix');


            $old_payments = DB::connection('mysqlImport')
                ->table($prefix.'_transactions')->whereOrderId($data->id)->get();
            foreach ($old_payments as $v) {
                $order->payments()
                    ->forceCreate([
                        'user_id' => 0,
                        'order_id' => $order->id,
                        'payment_account_id' => $v->pocket_type,
                        'description' => $v->transaction_notes,
                        'amount' => $v->transaction_sum,
                        'created_at' => $v->created_at,
                        'updated_at' => $v->created_at
                    ]);
            }


            $old_notes = DB::connection('mysqlImport')
                ->table($prefix.'_orders_touches_comments')->whereOrderId($data->id)->get();
            foreach ($old_notes as $v) {
                $order->notes()
                    ->forceCreate([
                        'order_id' => $order->id,
                        'user_id' => 0,
                        'is_pinned' => $v->type === 'comment' ? 1 : 0,
                        'text' => $v->text,
                        'created_at' => $v->created_at,
                        'updated_at' => $v->created_at
                    ]);
            }
        }


//        +"responsible_worker_id": 13
//        +"order_lost": 1
//        +"order_closed": 0
//        +"order_external": 0
//        +"multiple_dates": 0
//        +"trvl_fee": 0.0
//        +"min": 3.0
//        +"max": 3.0
//        +"price_per_hour": 115.0
//        +"fuel_exp": 0.0
//        +"claim": 0.0
//        +"referral": 0.0
//        +"total_estimate_min": 345.0
//        +"total_estimate_max": 345.0
//        +"extra_total": 0.0
//        +"total_trvl_fee": 0.0
//        +"price_per_hour_auto": 1
//        +"rate": "normal"
//        +"intrastate_coef": 0.0
//        +"intrastate_coef_auto": 1
//        +"interstate_coef": 0.0
//        +"interstate_coef_auto": 1
//        +"interstate_mode": 1
//        +"pick_up_shuttle": 0
//        +"delivery_shuttle": 0
//        +"shuttle_price": 0.0
//        +"elevator_charge": 0.0
//        +"stair_carry": 0.0
//        +"fuel_surcharge": 0.0
//        +"unpack_ser": 0.0
//        +"pack_ser": 0.0
//        +"interstate_par": "{"hours":3,"trvl_fee":0,"unpack_service":1,"fuel_surcharge":15,"stair_carry":75,"elevator_charge":50,"main_mode":1,"pack_service":1.5,"shuttle":[{"min":0,"max":100,"value":300},{"min":101,"max":200,"value":300},{"min":201,"max":300,"value":300},{"min":301,"max":400,"value":300},{"min":401,"max":500,"value":500},{"min":501,"max":600,"value":500},{"min":601,"max":700,"value":500},{"min":701,"max":800,"value":500},{"min":801,"max":900,"value":800},{"min":901,"max":1000,"value":800}]}"
//        +"delivery_days": ""
//        +"labor_and_transportation": 0.0
//        +"truck_time": "0"
//        +"pack_time": 0.0
//        +"truck_pack_time": 0
//        +"booked_date": null
//        +"discount": 0
    }

    public function h2hOrderStatus(int $from_status_id, $branch_id): int
    {
        if ($branch_id === 2) {
            return 5;
        }

        $list = [
            56 => 13,
            40 => 9,
            58 => 8,
            61 => 6,
            67 => 9,
            68 => 5,
            69 => 18,
            70 => 9,
            71 => 10,
            72 => 9,
            73 => 5,
            74 => 5,
            76 => 3,
            77 => 4,
            78 => 5,
            79 => 5,
            80 => 1,
            57 => 9,
            41 => 9,
            36 => 5,
            37 => 9,
            1 => 1,
            4 => 9,
            8 => 9,
            9 => 10,
            33 => 20,
            7 => 9,
        ];

        return $list[$from_status_id] ?? 9;
    }

    /**
     * Синхронизировать сотрудников (Дубли не создает).
     * @param  string  $prefix
     * @param  int  $division_id
     * @return void
     */
    private function syncEmployees(string $prefix, int $division_id)
    {
        echo 'Sync Employee'.PHP_EOL;

        Employee::whereJsonContains('division_ids', $division_id)
            ->get()
            ->map(function ($item) {
                $ex = explode(' id:', $item->l_name);

                if (isset($ex[1])) {
                    $this->employee2newId[$ex[1]] = $item->id;
                }

                return $item;
            });

        // Манагеров - всех, остальные - только активные
        $employees = DB::connection('mysqlImport')->table($prefix.'_workers')
            ->where('role_id', 5)
            ->orWhere('status', 'In Work')
            ->get();

        foreach ($employees as $v) {
            // Блокировка пустых имен + pavel
            if (!isset($this->employee2newId[$v->id]) && $v->name && $v->email !== 'pavel.snowdog@gmail.com') {

                $user = User::whereEmail($v->email)->first();
                if (!$user) {
                    $user = new User();
                    $user->name = $v->name.' '.$v->l_name;
                    $user->email = $v->email;
                    $user->password = 'none';
                    $user->save();
                    $user->roles()->attach($v->role_id);
                }

                $employee = new Employee();
                $employee->name = $v->name;
                $employee->l_name = $v->l_name.' id:'.$v->id;
                $employee->signature = $v->email_signature;
                $employee->created_at = $v->created_at;
                $employee->division_ids = [$division_id];
                $employee->active = $v->status === 'In Work';
                $employee->auth_user_id = $user->id;
                $employee->save();

                if ($v->phone) {
                    $employee->phones()
                        ->forceCreate([
                            'value' => $v->phone,
                            'type_id' => 1,
                            'employee_id' => $employee->id,
                        ]);
                }

                if ($v->email) {
                    $employee->emails()
                        ->forceCreate([
                            'value' => $v->email,
                            'employee_id' => $employee->id,
                        ]);
                }

                $this->employee2newId[$v->id] = $employee->id;
                echo '.';
            }
        }

        echo 'Users migrated'.PHP_EOL;
    }

    private function syncTrucks(string $prefix, int $division_id)
    {
        echo 'Trucks Employee'.PHP_EOL;

        $exists_trucks = [];
        Truck::whereJsonContains('division_ids', $division_id)
            ->get()
            ->map(function ($item) use (&$exists_trucks) {
                $ex = explode(' id:', $item->title);

                if (isset($ex[1])) {
                    $exists_trucks[$ex[1]] = $item->id;
                }

                return $item;
            });

        $trucks = DB::connection('mysqlImport')->table($prefix.'_trucks')
            ->get();

        foreach ($trucks as $v) {
            // Блокировка пустых имен + pavel
            if (!isset($exists_trucks[$v->id])) {
                $truck = new Truck();
                $truck->title = $v->truck_make.' id:'.$v->id;
                $truck->nickname = $v->truck_nickname;
                $truck->model = $v->truck_model;
                $truck->year = $v->truck_year;
                $truck->l_plate = $v->truck_l_plate;
                $truck->vin = $v->truck_vin;
                $truck->p_color = $v->truck_p_color;
                $truck->length = $v->truck_length > 0 ? $v->truck_length : null;
                $truck->division_ids = [$division_id];
                $truck->active = $v->truck_status === 'In Work';
                $truck->created_at = $v->created_at;
                $truck->updated_at = $v->updated_at;
                $truck->save();


                $exists_trucks[$v->id] = $truck->id;
                echo '.';
            }
        }

        echo 'Trucks migrated'.PHP_EOL;
    }
}
