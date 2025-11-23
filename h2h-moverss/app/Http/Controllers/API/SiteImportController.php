<?php

namespace App\Http\Controllers\API;

use App\Jobs\API\ZaiperNewLeadJob;
use App\Services\Communications\RecordCreateService;
use Carbon\CarbonImmutable;
use App\Http\Controllers\{Controller, WaypointController};
use App\Models\{BuildingType,
    Client,
    Communications\CommunicationRecord,
    MoveSize,
    Order,
    Order\Extended,
    Settings\EstimateParameters,
    Settings\WaypointFlights};
use DB, Exception, Http, Str, Log;

/**
 * Import orders from websites.
 * h2hmove.com AND h2hmovers.com
 */
class SiteImportController extends Controller
{
    /**
     * Project.
     * la OR h2h
     * @var string
     */
    private string $project;

    /**
     * Site domain.
     * @var string
     */
    private string $host;

    /**
     * @var WaypointController
     */
    private WaypointController $waypoint;

    /**
     * Waypoints Buffer
     * @var array
     */
    private array $waypoints_buff;
    private int $division_id;
    private array $flights;

    /**
     * Run import orders.
     * @param  string  $project
     * @return void
     * @throws \Throwable
     */
    public function importOrders(string $project): void
    {
//        logger_info('[import-wp] START IMPORT ORDERS', [
//            'project' => $project,
//        ]);

        $this->project = $project;
        $this->division_id = config("app.site_import.{$this->project}.division_id");
        $this->host = str_replace(['http://', 'https://'], '', config("app.site_import.{$this->project}.site_url"));
        $this->waypoint = new WaypointController();
        $this->flights = WaypointFlights::get(['id', 'title'])
            ->mapWithKeys(function ($item, $key) {
                return [$item->title => $item->id];
            })
            ->all();

        if ($project === 'la' || $project === 'h2h') {
            $this->importWordpressOrders();
        } else {
            echo 'Project not found o_0';
        }
    }

    /**
     * Import forms from LA.
     * @return void
     * @throws \Throwable
     */
    private function importWordpressOrders(): void
    {
//        logger_info('[import-wp] importWordpressOrders');
        foreach ([1, 5] as $form_id) {
            $latest = Extended::where('import->form_id', $form_id)
                ->where('import->host', $this->host)
                ->orderByRaw("CAST(JSON_EXTRACT(`import`,'$.id') as unsigned) DESC")
                ->first(); // TODO If it will slow down you can get past 200 PK order records
            $latest_id = (int) (!empty($latest->import['id']) ? $latest->import['id'] : 0);

            $params = [
//                'sorting[date_created]' => 'ASC', // API does not recognize sorting
                'paging[page_size]' => 500,
                'search[start_date]' => date('Y-m-d', strtotime('-7 days')),
                'search[end_date]' => now()->toDateString(),
            ];

            try {
                $res = $this->apiRequest('forms/'.$form_id.'/entries', $params);

//                logger_info('[import-wp] Response', ['data' => $res]);
                if ($res['status'] === 200) {
                    if ($res['response']['total_count']) {
                        $records = collect($res['response']['entries'])->sortBy('id');
                        foreach ($records as $v) {
                            $id = (int) $v['id'];

                            $exists = Extended::where('import->form_id', $form_id)
                                ->where('import->host', $this->host)
                                ->where('import->id', $id)
                                ->exists();
                            if ($exists) {
                                continue;
                            }

                            if ($latest_id < $id) {
                                DB::transaction(function () use ($v, &$latest_id, $id, $form_id) {
                                    echo 'Import order: '.$id.PHP_EOL.
                                        'Form id: '.$form_id.PHP_EOL;
                                    $this->importWordpressOrder($v, [
                                        'host' => $this->host,
                                        'id' => $id,
                                        'form_id' => $form_id,
                                    ]);
                                    $latest_id = $id;
                                    echo '.'.PHP_EOL;
                                });
                            }
                        }
                    }
                } else {
                    throw new Exception('API Error '.print_r($res, true));
                }
            } catch (Exception $e) {
                report($e);
                echo 'Error while executing import: '.$e->getMessage().' Line: '.$e->getLine();
            }
        }

        echo 'Finished'.PHP_EOL;
    }

    /**
     * Request to site API.
     * @param $route
     * @param  array  $append_params
     * @return array
     */
    private function apiRequest($route, array $append_params = []): array
    {
        $expires = strtotime('+60 min');
        $string_to_sign = sprintf('%s:%s:%s:%s', config("app.site_import.{$this->project}.mf_public"), 'GET', $route,
            $expires);
        $sig = $this->getSignature($string_to_sign, config("app.site_import.{$this->project}.mf_private"));
        $params = [
            'api_key' => config("app.site_import.{$this->project}.mf_public"),
            'signature' => $sig,
            'expires' => $expires,
        ];
        $params = array_merge($params, $append_params);

        $url = config("app.site_import.{$this->project}.site_url").'/gravityformsapi/'.$route.'?'.http_build_query($params);

//        dd($url);

        return Http::get($url)
            ->json();
    }

    /**
     * Get signature for API calling.
     * @param  string  $string
     * @param  string  $private_key
     * @return string
     */
    private function getSignature(string $string, string $private_key): string
    {
        $hash = hash_hmac('sha1', $string, $private_key, true);
        return rawurlencode(base64_encode($hash));
    }

    /**
     * Creating order with data.
     * @param  array  $entry
     * @param  array  $miscs
     * @return void
     * @throws Exception
     */
    private function importWordpressOrder(array $entry, array $miscs): void
    {
        $email = filter_var($entry['7'], FILTER_VALIDATE_EMAIL) ? $entry['7'] : null;
        $entry['5'] = strip_tags($entry['5']);
        $names = explode(' ', $entry['5']);
        $phone = preg_replace('/[^0-9]/', '', $entry['6']);
        $text_me = false;
        $source_id = null;

        $client_id = (new Client())->searchOrCreate([
            'name' => $names[0],
            'lname' => trim(str_replace($names[0], '', $entry['5'])),
        ], (array) $email, (array) $phone);

        $move_size_id = MoveSize::whereTitle($entry['4'])->first(['id']);
        $move_size_id = $move_size_id->id ?? null;

        $building_type_id = 1;
        if (!empty($entry['3'])) {
            $building_type_id = BuildingType::whereTitle($entry['3'])->first(['id']);
            $building_type_id = $building_type_id->id ?? 1;
        }

        if ($miscs['form_id'] === 1) {
            $source_name = $entry['12'] ?? null;

            if ($source_name) {
                $source = Order\Source::query()
                    ->whereJsonContains('division_ids', $this->division_id)
                    ->where('title', 'LIKE', $source_name)
                    ->first(['id']);
                $source_id = $source->id ?? null;
            }

            // may text me
            if (!empty($entry['47.1'])) {
                $text_me = true;
            }

            $params = $this->parseLaForm1($entry, (int) $building_type_id);
        } elseif ($miscs['form_id'] === 5) {
            $source_name = $entry['16'] ?? null;
            if (!empty($entry['17'])) {
                $source_name = $entry['17'];
            }

            if ($source_name) {
                $source = Order\Source::query()
                    ->whereJsonContains('division_ids', $this->division_id)
                    ->where('title', 'LIKE', $source_name)
                    ->first(['id']);
                $source_id = $source->id ?? null;
            }

            // may text me
            if (!empty($entry['53.1'])) {
                $text_me = true;
            }

            $params = $this->parseLaForm5($entry, (int) $building_type_id);
        } else {
            throw new Exception('There are no processing conditions for this form '.$miscs['form_id']);
        }

//        logger_info('[import-wp] Pars form data', ['params' => $params]);

        $type = null;
        if (!empty($entry['3'])) {
            if (str_contains($entry['3'], 'House')) {
                $type = 'house';
            } elseif ($entry['3'] === 'Apartment') {
                $type = 'apartment';
            } elseif ($entry['3'] === 'Storage') {
                $type = 'storage';
            } elseif (str_contains($entry['3'], 'Office')) {
                $type = 'business';
            }
        }

        try {
            // Create new Order
            $order = new Order();
            $order->client_id = $client_id;
            $order->division_id = $this->division_id;
            $order->status_id = 1;
            $order->sizing_is_auto = 1;
            $order->source_id = $source_id;
            $order->move_size_id = $move_size_id;
            $order->type = $type;
            $order->hash = Str::random(32);
            $order->first_calc_as_client = true;
            $order->save();
            $order->audits()->update([
                'division_id' => $this->division_id,
                'is_client_activity' => true,
            ]);

            $dataAuditUpdate = [
                'division_id' => $this->division_id,
                'is_client_activity' => true,
                'client_id' => $client_id,
                'order_id' => $order->id,
            ];

            $client = Client::find($client_id);
            if($client->created_at > CarbonImmutable::now()->subMinute()){
                $client->audits()->update($dataAuditUpdate);
                if($email = $client->emails()->first()){
                    $email->audits()->update($dataAuditUpdate);
                }
                if($phone = $client->phones()->first()){
                    $phone->audits()->update($dataAuditUpdate);
                }
            }

//            ZaiperNewLeadJob::dispatch([
//                'client_id' => $order->client_id,
//                'leadID' => $order->id,
//            ]);

            $min_params = EstimateParameters::where('estimate_type', 'local')
                ->get(['name', 'value'])
                ->pluck('value', 'name')
                ->all();

            $estimate = $order->estimate()->create([
                'type' => 'local',
                'trucks' => 1,
                'crews' => 2,
                'travel_fee' => !empty($min_params['travel_fee']) ? $min_params['travel_fee'] : 0.5,
                'fee_type' => !empty($min_params['fee_type']) ? $min_params['fee_type'] : 'percent',
            ]);
            $estimate->audits()
                ->latest()
                ->first()
                ->update($dataAuditUpdate);

            $extended = $order->extended()->create([
                'import' => $miscs,
            ]);
            $extended->audits()
                ->latest()
                ->first()
                ->update($dataAuditUpdate);

            $notes = 'Order from site: '.$this->host.' Form: '.$miscs['form_id'].' ID: '.$miscs['id'];

            if (!$source_id && $source_name) {
                $notes .= ' Source: '.$source_name;
            }

            if ($text_me) {
                $notes .= ' It\'s okay to text me';
            }

//        if ($miscs['form_id'] === 5 && !empty($entry['17'])) {
//            $notes .= ' Client notes: '.$entry['17'];
//        }

            $note = new Order\Notes();
            $note->order_id = $order->id;
            $note->user_id = 0;
            $note->text = $notes;
            $note->is_pinned = 1;
            $note->save();
            $note->audits()->update($dataAuditUpdate);

            RecordCreateService::handler($note);

            $work = $order->works()
                ->create([
                    'start_date' => $params['start_date'],
                    'start_time' => $params['start_time'] ?? null,
                    'duration' => $min_params['min_hours'],
                    'trucks' => 1,
                    'employees' => 2,
                ]);
            $work->workTypes()->sync($params['work_types']);
            $work->audits()->update($dataAuditUpdate);

            foreach ($params['waypoints'] as $v) {
                $waypoint = $order->waypoints()->create($v);
                $waypoint->audits()->update($dataAuditUpdate);
            }

            if ($params['inventory']) {
                foreach ($params['inventory'] as $v) {
                    $inventory = $order->inventories()
                        ->create([
                            'section_id' => 0,
                            'qty' => 1,
                            'sort' => 1,
                            'title' => $v,
                        ]);
                    $inventory->audits()->update($dataAuditUpdate);
                }
            }

            // Calculate route
            $wp_controller = new WaypointController();
            $wp_controller->recalculateDistance($order);

            $msg = 'New site '.$this->project.' import '.now()->toDateTimeString().' Order: '.$order->id;
            Log::info($msg, ['entry' => $entry, 'miscs' => $miscs]);
//            logger_info('[import] ORDER FROM WP', [
//                'msg' => $msg,
//                'entry' => $entry,
//                'miscs' => $miscs
//            ]);
        } catch (\Throwable $e) {
            dd($e);
        }
    }

    /**
     * Parsing form 1 for LA.
     * @param  array  $entry
     * @param  int  $building_type
     * @return array
     */
    private function parseLaForm1(array $entry, int $building_type): array
    {
        if (!empty($entry['11'])) {
            $prepare_time = str_replace(['(', ')'], '', $entry['11']);
        }

        $waypoints = [];
        if (!empty($entry['2'])) {
            $address = $this->waypoint->getAddressInfo((int) $entry['2']);

            $flights = $entry['9'] ?? null;
            $flights_id = 0;
            if (!empty($flights) && isset($this->flights[$flights])) {
                $flights_id = $this->flights[$flights];
            }

            $waypoints[] = [
                'type' => 'pickup',
                'zip' => substr((int) $entry['2'], 0, 5),
                'city' => $address['address_data']['locality'] ?? null,
                'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
                'address' => $address['formatted_address'] ?? null,
                'sort' => 1,
                'lat' => $address['geometry']['location']['lat'] ?? null,
                'lng' => $address['geometry']['location']['lng'] ?? null,
                'building_type_id' => $building_type,
                'parking_type_id' => 1,
                'flights_id' => $flights_id,
                'has_elevator' => !empty($flights) && $flights === 'Elevator' ? 1 : 0,
//                'ap',
            ];
        }
        if (!empty($entry['1'])) {
            $address = $this->waypoint->getAddressInfo((int) $entry['1']);

            $flights = $entry['10'] ?? null;
            $flights_id = 0;
            if (!empty($flights) && isset($this->flights[$flights])) {
                $flights_id = $this->flights[$flights];
            }

            $waypoints[] = [
                'type' => 'destination',
                'zip' => substr((int) $entry['1'], 0, 5),
                'city' => $address['address_data']['locality'] ?? null,
                'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
                'address' => $address['formatted_address'] ?? null,
                'sort' => 2,
                'lat' => $address['geometry']['location']['lat'] ?? null,
                'lng' => $address['geometry']['location']['lng'] ?? null,
                'building_type_id' => $building_type,
                'parking_type_id' => 1,
                'flights_id' => $flights_id,
                'has_elevator' => !empty($flights) && $flights === 'Elevator' ? 1 : 0,
            ];
        }

        $work_types = [];
        if (!empty($entry['14'])) {
            if ($entry['14'] === 'Packing Of Boxes Only') {
                $work_types = 2;
            } elseif ($entry['14'] === 'Packing Of Boxes & Moving') {
                $work_types = [1, 2];
            } elseif ($entry['14'] === 'Loading Only') {
                $work_types[] = 3;
            } elseif ($entry['14'] === 'Packing Of Boxes & Loading') {
                $work_types = [2, 3];
            } elseif ($entry['14'] === 'Unloading Only') {
                $work_types[] = 4;
            } elseif ($entry['14'] === 'Furniture Rearrangement Only') {
                $work_types[] = 5;
            } elseif ($entry['14'] === 'Moving & Junk Removal') {
                $work_types = [1, 6];
            } elseif ($entry['14'] === 'Junk Removal Only') {
                $work_types[] = 6;
            }
        }

        return [
            'start_date' => date('Y-m-d', strtotime($entry['8'])),
            'start_time' => isset($prepare_time) ? date('H:i:s', strtotime($prepare_time)) : null,
            'work_types' => $work_types ?: [1],
            'waypoints' => $waypoints,
            'inventory' => [],
        ];
    }

    /**
     * Parsing form 5 for LA
     * @param  array  $entry
     * @param  int  $building_type
     * @return array
     */
    private function parseLaForm5(array $entry, int $building_type): array
    {
        if (!empty($entry['11'])) {
            $prepare_time = str_replace(['(', ')'], '', $entry['11']);
        }
        $this->waypoints_buff = [];

        if (!empty($entry['14.5'])) {
            $flights = $entry['47'] ?? $entry['9'] ?? null;

            $this->wpParseLaAddress($entry['14.1'], $entry['14.4'], $entry['14.3'], $entry['14.5'],
                $flights, $building_type);
        }

        if (!empty($entry['38.5'])) {
            $flights = $entry['40'] ?? $entry['25'] ?? null;

            $this->wpParseLaAddress($entry['38.1'], $entry['38.4'], $entry['38.3'], $entry ['38.5'],
                $flights, $building_type);
        }
        if (!empty($entry['39.5'])) {
            $flights = $entry['40'] ?? $entry['44'] ?? null;

            $this->wpParseLaAddress($entry['39.1'], $entry['39.4'], $entry['39.3'], $entry['39.5'],
                $flights, $building_type);
        }
        if (!empty($entry['37.5'])) {
            $flights = $entry['10'] ?? null;
            $this->wpParseLaAddress($entry['37.1'], $entry['37.4'], $entry['37.3'], $entry['37.5'], $flights,
                $building_type);
        }

        $latest_index = count($this->waypoints_buff) - 1;
        if (isset($this->waypoints_buff[$latest_index])) {
            $this->waypoints_buff[$latest_index]['type'] = 'destination';
        }

        $work_types = [];
        if (!empty($entry['29'])) {
            if ($entry['29'] === 'Packing Of Boxes Only') {
                $work_types[] = 2;
            } elseif ($entry['29'] === 'Packing Of Boxes & Moving') {
                $work_types = [1, 2];
            } elseif ($entry['29'] === 'Loading Only') {
                $work_types[] = 3;
            } elseif ($entry['29'] === 'Packing Of Boxes & Loading') {
                $work_types = [2, 3];
            } elseif ($entry['29'] === 'Unloading Only') {
                $work_types[] = 4;
            } elseif ($entry['29'] === 'Furniture Rearrangement Only') {
                $work_types[] = 5;
            } elseif ($entry['29'] === 'Moving & Junk Removal') {
                $work_types = [1, 6];
            } elseif ($entry['29'] === 'Junk Removal Only') {
                $work_types[] = 6;
            }
        }

        return [
            'start_date' => date('Y-m-d', strtotime($entry['8'])),
            'start_time' => isset($prepare_time) ? date('H:i:s', strtotime($prepare_time)) : null,
            'work_types' => $work_types ?: [1],
            'waypoints' => $this->waypoints_buff,
            'inventory' => !empty($entry['13']) ? $this->parseInventory($entry['13']) : null,
        ];
    }

    /**
     * Get data for waypoint and fill waypoints_buff.
     * @param $address
     * @param $state
     * @param $city
     * @param $zip
     * @param $flights
     * @param $building_type
     * @return void
     */
    private function wpParseLaAddress($address, $state, $city, $zip, $flights, $building_type): void
    {
        $g_address = $this->waypoint->getAddressInfo((int) $zip);

        $state = !empty($state) ? $state : ($g_address['address_data']['administrative_area_level_1'] ?? 'NA');
        if (!empty($city)) {
            $city = Str::limit($city, 45);
        }
        if (!empty($address)) {
            $address = Str::limit($address, 145);
        }

        $v = [
            'type' => 'pickup',
            'zip' => substr((int) $zip, 0, 5),
            'city' => !empty($city) ? $city : ($g_address['address_data']['locality'] ?? null),
            'state' => strtoupper(mb_substr($state, 0, 2)),
            'address' => !empty($address) ? $address : ($g_address['formatted_address'] ?? null),
            'sort' => count($this->waypoints_buff) + 1,
            'lat' => $g_address['geometry']['location']['lat'] ?? null,
            'lng' => $g_address['geometry']['location']['lng'] ?? null,
            'building_type_id' => $building_type,
            'parking_type_id' => 1,
            'has_elevator' => !empty($flights) && $flights === 'Elevator' ? 1 : 0,
//            'ap'
        ];

        if (!empty($flights) && isset($this->flights[$flights])) {
            $v['flights_id'] = $this->flights[$flights];
        }

        $this->waypoints_buff[] = $v;
    }

    /**
     * Parse inventory.
     * @param $string
     * @return array
     */
    private function parseInventory($string): array
    {
        $rows = [];

        $string = trim(strip_tags($string));
        if ($string) {
            $ex = preg_split('/(,\s|\|;|\n)/', $string);

            foreach ($ex as $v) {
                $v = trim($v);
                if ($v) {
                    $rows[] = Str::limit($v, 95);
                }
            }
        }

        return $rows;
    }

}
