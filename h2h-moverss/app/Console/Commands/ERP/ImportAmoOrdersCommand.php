<?php

namespace App\Console\Commands\ERP;

use App\Http\Controllers\WaypointController;
use App\Models\{Client, Order, Settings\EstimateParameters};
use Illuminate\Console\Command;
use DB, Str;

class ImportAmoOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:import-amo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Залить заказы с другой таблицы, вливаем при факапе';

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
        $branch_id = 2;

        $orders = DB::connection('mysqlImport')
            ->table('Sheet1307')
            ->selectRaw('*, STR_TO_DATE(Move_date, \'%d.%m.%Y %k:%i:%s\') as move_on')
            ->where('Pipeline', 'New leads California')
            ->havingRaw('`move_on` >= \'2021-07-14\'')
            ->get();

        foreach ($orders as $order) {
            $find = Order::where('division_id', $branch_id)
                ->whereHas('extended', function ($q) use ($order) {
                    $q->where('import->provider', 'amo')
                        ->where('import->id', $order->ID);
                })
                ->first();
            if ($find) {
                continue;
            }

            DB::transaction(function () use ($order, $branch_id) {
                $this->import($order, $branch_id);
            });
        }
    }

    public function import($entry, $branch_id)
    {
        echo 'Importing '.$entry->ID.PHP_EOL;
        $emails = $this->explodeValues($entry->{'Work_email_(contact)'});

        if (strstr($entry->{'Work_phone_(contact)'}, 'ext')) {
            $entry->{'Work_phone_(contact)'} = '';
        }

        $all_p = array_merge($this->explodeValues($entry->{'Mobile_phone_(contact)'}),
            $this->explodeValues($entry->{'Work_phone_(contact)'}));
        $phones = collect($all_p)
            ->transform(function ($phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);

                return substr($phone, 0, 1) === '1' ? substr($phone, 1) : $phone;
            })
            ->all();

        $client_id = (new Client())->searchOrCreate([
            'name' => $entry->Lead_title,
            'lname' => null,
        ], $emails, $phones);

        $move_size_id = $this->moveSize($entry->{'SIZE_OF_MOVE*'});
        $building_type = $this->buildType($entry->{'TYPE_OF_MOVE*'});
        $source_id = $this->getSource($entry->{'HOW_DID_YOU_HEAR_ABOUT_US?*'});
        $services_ids = $this->getServices($entry->{'SERVICES_REQUESTED*'});

        $notes = [];
        $notes[] = 'https://h2hmovers.amocrm.com/leads/detail/'.$entry->ID;
        for ($i = 1; $i <= 5; $i++) {
            $field = 'Note_'.$i;
            if (!empty($v->{$field})) {
                $notes[] = $v->{$field};
            }
        }
        if (!empty($entry->{'Lead_tags'})) {
            $notes[] = 'amoCRM Tags: '.$entry->{'Lead_tags'};
        }
        if (!empty($entry->{'Lead_stage'})) {
            $notes[] = 'amoCRM Stage: '.$entry->{'Lead_stage'};
        }

        if ($entry->{'Lead_stage'} == 'calculations done/offer sent') {
            $notes[] = $entry->{'Lead_stage'};
        } elseif ($entry->{'Lead_stage'} == 'Request Form received/fulfilled') {
            $notes[] = $entry->{'Lead_stage'};
        }


        // Создаем заказ
        $order = new Order();
        $order->client_id = $client_id;
        $order->division_id = $branch_id;
        $order->status_id = 19;
        $order->source_id = $source_id;
        $order->move_size_id = $move_size_id;
        $order->hash = Str::random(32);
        $order->save();

        $type = 'local';
        if ($entry->{'Move_Type'} == 'Intra-state') {
            $type = 'intrastate';
        } elseif ($entry->{'Move_Type'} == 'Long distance') {
            $type = 'interstate';
        }

        $order->estimate()->create([
            'type' => $type,
            'trucks' => 1,
            'crews' => 2,
        ]);

        $order->extended()->create([
            'import' => [
                'provider' => 'amo',
                'id' => $entry->ID,
            ],
        ]);

        $note = new Order\Notes();
        $note->order_id = $order->id;
        $note->user_id = 0;
        $note->text = implode(PHP_EOL, $notes);
        $note->is_pinned = 1;
        $note->save();

        $min_params = EstimateParameters::where('estimate_type', 'local')
            ->get(['name', 'value'])
            ->pluck('value', 'name')
            ->all();

        $work = $order->works()
            ->create([
                'start_date' => date('Y-m-d', strtotime($entry->move_on)),
                'start_time' => date('H:i:s', strtotime($entry->move_on)),
                'duration' => $min_params['min_hours'],
                'trucks' => 1,
                'employees' => 2,
            ]);
        $work->workTypes()->sync($services_ids);


        $wp_controller = new WaypointController();
        $waypoints = [];
        if (!empty($entry->{'FROM__ZIP*'})) {
            $address = $wp_controller->getAddressInfo((int) $entry->{'FROM__ZIP*'});

            $waypoints[] = [
                'type' => 'pickup',
                'zip' => substr((int) $entry->{'FROM__ZIP*'}, 0, 5),
                'city' => $address['address_data']['locality'] ?? null,
                'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
                'address' => 'From API by ZIP: '.($address['formatted_address'] ?? null),
                'sort' => 1,
                'lat' => $address['geometry']['location']['lat'] ?? null,
                'lng' => $address['geometry']['location']['lng'] ?? null,
                'building_type_id' => $building_type,
                'parking_type_id' => 1,
                'has_elevator' => stristr($entry->{'STAIRS*1'}, 'Elevator') ? 1 : 0,
            ];
            if (!empty($entry->{'TO_ZIP*'})) {
                $address = $wp_controller->getAddressInfo((int) $entry->{'TO_ZIP*'});

                $waypoints[] = [
                    'type' => 'destination',
                    'zip' => substr((int) $entry->{'FROM__ZIP*'}, 0, 5),
                    'city' => $address['address_data']['locality'] ?? null,
                    'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
                    'address' => 'From API by ZIP: '.($address['formatted_address'] ?? null),
                    'sort' => 2,
                    'lat' => $address['geometry']['location']['lat'] ?? null,
                    'lng' => $address['geometry']['location']['lng'] ?? null,
                    'building_type_id' => $building_type,
                    'has_elevator' => stristr($entry->{'STAIRS*2'}, 'Elevator') ? 1 : 0,
                    'parking_type_id' => 1,
                ];
            }
        }
        foreach ($waypoints as $v) {
            $order->waypoints()->create($v);
        }

        // Пнуть посчитать маршрут
        $wp_controller = new WaypointController();
        $wp_controller->recalculateDistance($order);
    }

    private function explodeValues($values)
    {
        $values = preg_split('/, /', $values);
        return array_filter($values);
    }

    private function moveSize($string)
    {
        if ($string === '1 Bedroom') {
            return 2;
        } elseif ($string === '2 Bedroom') {
            return 3;
        } elseif ($string === '3 Bedroom') {
            return 4;
        } elseif ($string === '4+ Bedroom') {
            return 5;
        } elseif ($string === 'Studio') {
            return 1;
        }

        return null;
    }

    private function buildType($string)
    {
        if ($string === 'Business') {
            return 4;
        } elseif ($string === 'House') {
            return 1;
        } elseif ($string === 'Storage') {
            return 3;
        }

        return 2;
    }

    private function getSource($param)
    {
        $statuses = [
            ['id' => '1', 'name' => 'Yelp'],
            ['id' => '2', 'name' => 'Search Engine'],
            ['id' => '3', 'name' => 'Social Media'],
            ['id' => '4', 'name' => 'Referral'],
            ['id' => '5', 'name' => 'Angie\'s List'],
            ['id' => '6', 'name' => 'Newspaper'],
            ['id' => '7', 'name' => 'Local Ad'],
            ['id' => '8', 'name' => 'Classified Ad'],
            ['id' => '9', 'name' => 'Flyer'],
            ['id' => '10', 'name' => 'Returning Customer'],
            ['id' => '11', 'name' => 'Other'],
            ['id' => '12', 'name' => 'Paul Lawrence'],
            ['id' => '13', 'name' => 'iReloc'],
            ['id' => '14', 'name' => 'MoveMatcher'],
            ['id' => '15', 'name' => 'Storage Refferal'],
            ['id' => '16', 'name' => 'Hello Alfred'],
            ['id' => '17', 'name' => '123movers.com'],
            ['id' => '18', 'name' => 'BBB'],
            ['id' => '19', 'name' => 'Porch'],
            ['id' => '20', 'name' => 'Homeadvisor'],
            ['id' => '21', 'name' => 'Good Moving Leads'],
            ['id' => '22', 'name' => 'movingcompanyreviews.com'],
            ['id' => '23', 'name' => 'movingcompanyreviews.com'],
            ['id' => '24', 'name' => 'movingcompanyreviews.com'],
            ['id' => '25', 'name' => 'Hello Alfred'],
            ['id' => '26', 'name' => 'Moved.Com'],
            ['id' => '27', 'name' => 'Equate Media'],
            ['id' => '28', 'name' => 'Equate Media'],
            ['id' => '29', 'name' => 'Billy.Com'],
            ['id' => '30', 'name' => 'Thumbtack'],
            ['id' => '31', 'name' => 'DCH MANAGEMENT ( OPTIMA BUILDING SKOKIE)'],
            ['id' => '32', 'name' => 'DCH MANAGEMENT ( OPTIMA )'],
            ['id' => '33', 'name' => 'Eugenie Terrace on the Park'],
            ['id' => '34', 'name' => 'Hawthorn Apartments'],
            ['id' => '35', 'name' => 'Moving Service Team'],
            ['id' => '38', 'name' => 'Test'],
            ['id' => '39', 'name' => 'Test 2']
        ];

        foreach ($statuses as $v) {
            if (stristr($param, $v['name'])) {
                return $v['id'];
            }
        }

        return null;
    }

    private function getServices($param)
    {
        $serv = [
            ['id' => '1', 'title' => 'Moving'],
            ['id' => '2', 'title' => 'Packing'],
            ['id' => '3', 'title' => 'Loading'],
            ['id' => '4', 'title' => 'Unloading'],
            ['id' => '5', 'title' => 'Rearrangement'],
            ['id' => '6', 'title' => 'Junk'],
            ['id' => '8', 'title' => 'Unpacking'],
            ['id' => '9', 'title' => 'In-Home Estimate']
        ];

        $services = [];
        foreach ($serv as $v) {
            if (stristr($param, $v['title'])) {
                $services[] = $v['id'];
            }

        }
        if ($services) {
            return array_unique($services);
        } else {
            return [1];
        }
    }

}
