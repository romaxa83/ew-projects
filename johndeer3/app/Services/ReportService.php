<?php
//
//namespace App\Services;
//
//use App\Helpers\DateFormat;
//use App\Helpers\ReportHelper;
//use App\Helpers\StrHelper;
//use App\Http\Request\Report\AttachVideoRequest;
//use App\Models\Image;
//use App\Models\Report\Feature\ReportFeaturePivot;
//use App\Models\Report\Feature\ReportFeatureValue;
//use App\Models\Report\Feature\ReportValue;
//use App\Models\Report\Location;
//use App\Models\Report\Report;
//use App\Models\Report\ReportClient;
//use App\Models\Report\ReportMachine;
//use App\Models\Report\ReportPushData;
//use App\Models\Report\Video;
//use App\Models\User\User;
//use App\Repositories\Feature\FeatureRepository;
//use App\Repositories\JD\ClientRepository;
//use App\Repositories\JD\EquipmentGroupRepository;
//use App\Services\Telegram\TelegramDev;
//use App\Traits\StoragePath;
//use App\Type\ReportStatus;
//use Carbon\Carbon;
//use Carbon\CarbonImmutable;
//use Illuminate\Http\UploadedFile;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Storage;
//
//class ReportService
//{
//    use StoragePath;
//
//    private $clientRepository;
//    private $equipmentGroupRepository;
//    private $featuresRepository;
//
//    public function __construct(
//        ClientRepository $clientRepository,
//        EquipmentGroupRepository $equipmentGroupRepository,
//        FeatureRepository $featuresRepository
//    )
//    {
//        $this->clientRepository = $clientRepository;
//        $this->equipmentGroupRepository = $equipmentGroupRepository;
//        $this->featuresRepository = $featuresRepository;
//    }
//
//    /**
//     * @param array $request
//     * @param User $user
//     * @throws \Exception
//     */
//    public function create(array $request, User $user): ?Report
//    {
//        $dealerName = $user->dealer->name;
//
//        $report = new Report();
//        $report->status = $request['status'];
//        $report->user_id = $user->id;
//        $report->salesman_name = $request['salesman_name'] ?? null;
//        $report->assignment = $request['assignment'] ?? null;
//        $report->result = $request['result'] ?? null;
//        $report->client_comment = $request['client_comment'] ?? null;
//        $report->client_email = $request['client_email'] ?? null;
//
//        DB::beginTransaction();
//        try {
//            $report->save();
//
//            // запланированная дата, для уведомлений
//            $pushData = new ReportPushData();
//            if (isset($request['planned_at'])){
//                $pushData->planned_at = DateFormat::timestampMsToDate($request['planned_at']);
//            }
//            $report->pushData()->save($pushData);
//
//            if(isset($request['location']) && !empty($request['location'])){
//                $this->saveLocation($request['location'], $report->id);
//            }
//
//            $nameModel = null;
//            $nameClient = null;
//
//            foreach($request['clients'] ?? [] as $client){
//                if($this->isClientId($client)){
//                    // привязываем клиента из jd1
//                    $cli = $this->clientRepository->getById($client['client_id']);
//                    $attachClient = $report->clients();
//                } else {
//                    // создаем клиента для отчета
//                    $cli = $this->createReportClient($client);
//                    $attachClient = $report->reportClients();
//                }
//
//                $attachClient->attach($cli,[
//                    'type' => $client['type'] ?? null,
//                    'model_description_id' => $client['model_description_id'] ?? null,
//                    'quantity_machine' => $client['quantity_machine'] ?? null
//                ]);
//
//                if($nameClient == null){
//                    $nameClient = $cli->company_name;
//                }
//            }
//
//            foreach($request['machines'] ?? [] as $machine){
//                $mac = $this->createReportMachine($machine);
//                $report->reportMachines()->attach($mac);
//
//                if($nameModel == null){
//                    $nameModel = $mac->modelDescription ? $mac->modelDescription->name : $mac->model_description;
//                }
//            }
//
//            // сохраняем характеристики
//            if (isset($request['features']) && !empty($request['features'])){
//                $this->saveFeatures($request['features'], $report->id);
//                if($report->features->isNotEmpty()){
//                    $report->fill_table_date = CarbonImmutable::now();
//                }
//            }
//
//            if(isset($request['files']) && !empty($request['files'])){
//                $this->saveReportImages($request['files'], $report->id);
//            }
//
//            // создание и сохранения подписи из байтов
//            if(isset($request['files'][Image::SIGNATURE]) && !empty($request['files'][Image::SIGNATURE])){
//                $this->createSignatureFromBytes($request['files'][Image::SIGNATURE][0], $report->id);
//            }
//
//            $title = $dealerName .'_'.$nameClient.'_'.$nameModel.'_'.DateFormat::forTitle($report->created_at);
//
//            $report->title = ReportHelper::prettyTitle($title);
//            $report->save();
//
//            DB::commit();
//
//            return $report;
//        } catch(\Exception $exception) {
//            DB::rollBack();
//            \Log::error($exception->getMessage());
//
//            throw new \Exception($exception->getMessage());
//        }
//    }
//
//    /**
//     * @param $request
//     * @param Report $report
//     * @param User $user
//     * @return Report
//     * @throws \Exception
//     */
//    public function updatePs($request, Report $report, User $user): Report
//    {
//        // получаем eq ,чтоб проверить если у него характеристики, т.к. характеристики которые
//        // заплняються в отчете, привязанны к eg (у каждой eg свой набор характеристик)
//        $eq = false;
//        if (isset($request['machines'][0]['equipment_group_id']) && is_numeric($request['machines'][0]['equipment_group_id'])){
//            $eq = $this->equipmentGroupRepository->getById($request['machines'][0]['equipment_group_id']);
//        }
//
//        $dealerName = $user->dealer->name;
//
//        $report->status = $request->status ?? $report->status;
//        $report->salesman_name = $request->salesman_name ?? $report->salesman_name;
//        $report->assignment = $request->assignment ?? $report->assignment;
//        $report->client_comment = $request->client_comment ?? $report->client_comment;
//        $report->client_email = $request->client_email ?? $report->client_email;
//
//        if($eq){
//            if($eq->features->isNotEmpty()){
//                $report->result = null;
//            } else {
//                $report->result = $request->result ?? null;
//            }
//        }
//
//        if(isset($request['result'])){
//            $report->result = $request['result'];
//        }
//
//        DB::beginTransaction();
//        try {
//            $report->save();
//
//            // планируемая дата
//            if (isset($request['planned_at'])){
//                $plannedAt = DateFormat::timestampMsToDate($request['planned_at']);
//                if(isset($report->pushData)){
//                    if($report->pushData->planned_at){
//                        // если пришедшая "планируемая" дата не совпадает с уже существуещей, то перезаписуем
//                        if(!$report->pushData->equalsPlannedDate($plannedAt)){
//                            $oldDate = $report->pushData->planned_at;
//                            $report->pushData->prev_planned_at = $oldDate;
//                            $report->pushData->planned_at = $plannedAt;
//                            $report->pushData->is_send_start_day = false;
//                            $report->pushData->is_send_end_day = false;
//                            $report->pushData->is_send_week = false;
//                            // @todo dev
//                            TelegramDev::info("EDIT REPORT [{$report->id}], установлена НОВАЯ планируемая дата [{$plannedAt}], prevData - [{$oldDate}]", $user->login . " [{$user->id}]");
//                        }
//                    } else {
//                        $report->pushData->planned_at = $plannedAt;
//                        // @todo dev
//                        TelegramDev::info("EDIT REPORT [{$report->id}], установлена планируемая дата [{$plannedAt}]", $user->login . " [{$user->id}]");
//                    }
//
//                    $report->pushData()->save($report->pushData);
//                } else {
//                    // создаем данные , актуально для старых отчетов
//                    $pushData = new ReportPushData();
//                    $pushData->planned_at = $plannedAt;
//                    $report->pushData()->save($pushData);
////                    TelegramDev::info("EDIT REPORT [{$report->id}], установлена планируемая дата [{$plannedAt}]", $user->login . " [{$user->id}]");
//                }
//            }
//
//            // сохраняем локацию, если она пришла и ее еще нет у отчета
//            if(isset($request['location']) && !empty($request['location']) && ($report->location == null)){
//                $this->saveLocation($request['location'], $report->id);
//            }
//
//            $nameModel = null;
//            $nameClient = null;
//
//            $report->clients()->detach($report->clients);
//            $report->reportClients()->delete();
//            foreach($request->clients ?? [] as $client){
//                if($this->isClientId($client)){
//                    // привязываем клиента из jd1
//                    $cli = $this->clientRepository->getById($client['client_id']);
//                    $attachClient = $report->clients();
//                } else {
//                    // создаем клиента для отчета
//                    $cli = $this->createReportClient($client);
//                    $attachClient = $report->reportClients();
//                }
//                $attachClient->attach($cli,[
//                    'type' => $client['type'] ?? null,
//                    'model_description_id' => $client['model_description_id'] ?? null,
//                    'quantity_machine' => $client['quantity_machine'] ?? null
//                ]);
//
//                if($nameClient == null){
//                    $nameClient = $cli->company_name;
//                }
//            }
//
//            $report->reportMachines()->delete();
//            foreach($request->machines ?? [] as $machine){
//                $mac = $this->createReportMachine($machine);
//                $report->reportMachines()->attach($mac);
//
//                if($nameModel == null){
//                    $nameModel = $mac->modelDescription ? $mac->modelDescription->name : $mac->model_description;
//                }
//            }
//
//            // если изменился eg и у него нет привязаных характеристик, то удаляем их и не привязываем новые
//            if($eq){
//                if($eq->features->isNotEmpty()){
//                    if (isset($request['features']) && !empty($request['features'])){
//                        // удаляем старые характеристики
//                        $this->removeFeature($report);
//                        $this->saveFeatures($request['features'], $report->id);
//                        if(!$report->fill_table_date){
//                            $report->fill_table_date = Carbon::now();
//                        }
//                    } else {
//                        if($report->features){
//                            $this->removeFeature($report);
//                            $report->fill_table_date = null;
//                        }
//                    }
//                } else {
//                     //удаляем старые характеристики
//                    $this->removeFeature($report);
//                    $report->fill_table_date = null;
//                }
//            }
//
//            $deleteImg = [];
//            if(isset($request['files']) && !empty($request['files'])){
//                $deleteImg = $this->updateFile($request['files'], $report->id);
//            }
//
//            // подпись
//            if(isset($request['files'][Image::SIGNATURE]) && !empty($request['files'][Image::SIGNATURE])){
//                // если подпись есть у отчета , то не создаем
//                $existSignature = Image::query()
//                    ->where('entity_id', $report->id)
//                    ->where('model', Image::SIGNATURE)
//                    ->exists();
//
//                if(!$existSignature){
//                    $this->createSignatureFromBytes($request['files'][Image::SIGNATURE][0], $report->id);
//                }
//            }
//
//            $title = $dealerName .'_'.$nameClient.'_'.$nameModel.'_'.DateFormat::forTitle($report->created_at);
//            $report->title = ReportHelper::prettyTitle($title);
//            $report->save();
//
//            DB::commit();
//            // удаляем файлы с диска
//            $this->deleteFilesByLink($deleteImg);
//            $report->refresh();
//
//            return $report;
//        } catch(\Exception $exception) {
//            DB::rollBack();
//            \Log::error($exception->getMessage());
//
//            throw new \Exception($exception->getMessage());
//        }
//    }
//
//    /**
//     * @param $request
//     * @param Report $report
//     * @return Report|null
//     * @throws \Exception
//     */
//    public function update($request, Report $report): ?Report
//    {
//        $report->load(['clients', 'reportClients', 'reportMachines']);
//
//        $report->title = $request->title ? $request->title : $report->title;
//        $report->salesman_name = $request->salesman_name ? $request->salesman_name : $report->salesman_name;
//        $report->result = $request->result ? $request->result : $report->result;
//        $report->assignment = $request->assignment ? $request->assignment : $report->assignment;
//        $report->client_comment = $request->client_comment ? $request->client_comment : $report->client_comment;
//        $report->client_email = $request->client_email ? $request->client_email : $report->client_email;
//
//        DB::beginTransaction();
//
//        try {
//            foreach($request->machines ?? [] as $machine){
//                /** @var $mac ReportMachine */
//                $mac = $report->reportMachines->where('id', $machine['id'])->first();
//
//                $mac->header_brand_id = $machine['header_brand_id'] ?? $mac->header_brand_id;
//                $mac->header_model_id = $machine['header_model_id'] ?? $mac->header_model_id;
//                $mac->serial_number_header = $machine['serial_number_header'] ?? $mac->serial_number_header;
//                $mac->machine_serial_number = $machine['machine_serial_number'] ?? $mac->machine_serial_number;
//                $mac->trailed_equipment_type = $machine['trailed_equipment_type'] ?? $mac->trailed_equipment_type;
//                $mac->trailer_model = $machine['trailer_model'] ?? $mac->trailer_model;
//                $mac->model_description_id = $machine['model_description_id'] ?? $mac->model_description_id;
//                $mac->equipment_group_id = $machine['equipment_group_id'] ?? $mac->equipment_group_id;
//
//                $mac->save();
//            }
//
//            if(isset($request['clients']) && !empty($request['clients'])){
//                foreach ($request['clients'] as $clientType => $clients){
//                    if($clientType == 'john_dear_client'){
//                        foreach ($clients as $key => $client){
//                            if(isset($client['quantity_machine'])){
//                                $report->clients()
//                                    ->where('id', $report->clients[$key]->id)
//                                    ->updateExistingPivot($report->clients[$key]->id,[
//                                        'quantity_machine' => $client['quantity_machine']
//                                    ]);
//                            }
//                        }
//                    }
//                    if($clientType == 'report_client'){
//                        foreach ($clients as $key => $client){
//                            if(isset($client['quantity_machine'])){
//                                $report->reportClients()
//                                    ->where('id', $report->reportClients[$key]->id)
//                                    ->updateExistingPivot($report->reportClients[$key]->id,[
//                                        'quantity_machine' => $client['quantity_machine']
//                                    ]);
//                            }
//                        }
//                    }
//                }
//            }
//
//            if($report->comment){
//                $report->comment->text = $request->comment;
//                $report->comment->save();
//            }
//
//            $report->save();
//
//            DB::commit();
//            $report->refresh();
//
//            return $report;
//        } catch(\Exception $exception) {
//            DB::rollBack();
//            \Log::error($exception->getMessage());
//            throw new \Exception($exception->getMessage());
//        }
//    }
//
//    // @todo удалить обновление поля verify (также удалить из бд)
//    public function verify(Report $report): Report
//    {
//        if(!ReportStatus::canToggleToVerify($report->status)){
//            throw new \Exception(__('message.cannot toggle report to verify status'));
//        }
//
//        $report->verify = true;
//        $this->changeStatus($report, ReportStatus::VERIFY);
//
//        $report->save();
//
//        return $report;
//    }
//
//    public function changeStatus(Report $report, $status): Report
//    {
//        $report->status = $status;
//        $report->save();
//
//        return $report;
//    }
//
//    private function isClientId($data): bool
//    {
//        return isset($data['client_id']) && !empty($data['client_id']);
//    }
//
//    private function saveLocation(array $data, $reportId)
//    {
//        $location = new Location();
//        $location->report_id = $reportId;
//        $location->lat = $data['location_lat'] ?? null;
//        $location->long = $data['location_long'] ?? null;
//        $location->country = $data['location_country'] ?? null;
//        $location->city = $data['location_city'] ?? null;
//        $location->region = $data['location_region'] ?? null;
//        $location->zipcode = $data['location_zipcode'] ?? null;
//        $location->street = $data['location_street'] ?? null;
//        $location->district = $data['location_district'] ?? null;
//
//        $location->save();
//
//        return $location;
//    }
//
//    private function createReportClient($data): ReportClient
//    {
//        $reportClient = new ReportClient();
//        $reportClient->customer_id = $data['customer_id'] ?? null;
//        $reportClient->customer_first_name = $data['customer_first_name'] ?? null;
//        $reportClient->customer_last_name = $data['customer_last_name'] ?? null;
//        $reportClient->company_name = $data['company_name'] ?? null;
//        $reportClient->phone = $data['customer_phone'] ?? null;
//        $reportClient->comment = $data['comment'] ?? null;
//
//        $reportClient->save();
//
//        return $reportClient;
//    }
//
//    private function createReportMachine($data): ReportMachine
//    {
//        $reportMachine = new ReportMachine();
//        $reportMachine->manufacturer_id = $data['manufacturer_id'] ?? null;
//        $reportMachine->equipment_group_id = $data['equipment_group_id'] ?? null;
//        $reportMachine->model_description_id = $data['model_description_id'] ?? null;
//        $reportMachine->trailed_equipment_type = $data['trailed_equipment_type'] ?? null;
//        $reportMachine->trailer_model = $data['trailer_model'] ?? null;
//        $reportMachine->header_brand_id = $data['header_brand_id'] ?? null;
//        $reportMachine->header_model_id = $data['header_model_id'] ?? null;
//        $reportMachine->serial_number_header = $data['serial_number_header'] ?? null;
//        $reportMachine->machine_serial_number = $data['machine_serial_number'] ?? null;
//        $reportMachine->sub_machine_serial_number = $data['sub_machine_serial_number'] ?? null;
//        $reportMachine->sub_equipment_group_id = $data['sub_equipment_group_id'] ?? null;
//        $reportMachine->sub_model_description_id = $data['sub_model_description_id'] ?? null;
//        $reportMachine->sub_manufacturer_id = $data['sub_manufacturer_id'] ?? null;
//
//        $reportMachine->save();
//
//        return $reportMachine;
//    }
//
//    private function saveReportImages($arrayFiles, $reportId)
//    {
//        foreach ($arrayFiles ?? [] as $module => $files){
//            // подпись сохраняем отдельно
//            if($module != Image::SIGNATURE){
//                foreach($files ?? [] as $key => $item){
//                    $this->saveOneImage($item, $reportId, $module);
//                }
//            }
//        }
//    }
//
//    private function updateFile($files, $reportId)
//    {
//        $deleteFile = [];
//        $existFile = [];
//
//        foreach ($files as $moduleName => $items){
//            // если массив пришел пустой, то удаляем все файлы
//            if(empty($items)){
//                $existFile[$moduleName] = [];
//            }
//
//            if($moduleName != Image::SIGNATURE){
//                foreach ($items ?? [] as $key => $item){
//                    if($item instanceof UploadedFile){
//                        $existFile[$moduleName][$key] = $this->saveOneImage($item, $reportId, $moduleName);
//                    }
//
//                    if(is_string($item)){
//                        $existFile[$moduleName][$key] = $item;
//                    }
//                }
//            }
//        }
//        // удаляем файлы
//        foreach ($existFile as $moduleName => $urlFile){
//            $urlFiles = [];
//            foreach($urlFile as $key => $item){
//                $urlFiles[$key] = last(explode('/', $item));
//            }
//
//            $models = Image::query()
//                ->where('model', $moduleName)
//                ->where('entity_id', $reportId)
//                ->whereNotIn('basename', array_values($urlFiles))
//                ->where('model', '!=', Image::SIGNATURE)
//                ->get();
//
//            foreach ($models as $model){
//                $deleteFile[] = $model->url;
//                $model->forceDelete();
//            }
//        }
//
//        return $deleteFile;
//    }
//
//    private function saveOneImage($file, $reportId, $key = 'some')
//    {
//        /** @var $file UploadedFile */
//        $basename = $file->getClientOriginalName();
//
//        Storage::disk('public')
//            ->putFileAs('report/'.$reportId, $file, $basename);
//
//        Image::create(
//            $key, Report::class, $reportId, "report/{$reportId}/{$basename}", $basename, $file
//        );
//
//        return $basename;
//    }
//
//    // todo - вынести в отдельный сервис
//    public function saveFeatures(array $features, $reportId)
//    {
//        try {
//            foreach($features as $key => $feature){
////                // сохраняем только активированные характеристики
//                if($this->featuresRepository->activeFeature($feature['id'])){
//                    foreach($feature['group'] ?? [] as $value){
//
//                        $v = new ReportValue();
//                        $v->value = $value['value'] ?? null;
//                        $v->model_description_id = $value['id'] ?? null;
//                        $v->model_description_name = $value['name'] ?? null;
//                        $v->value_id = $value['choiceId'] ?? null;
//                        $v->save();
//
//                        $r = new ReportFeatureValue();
//                        $r->report_id = $reportId;
//                        $r->feature_id = $feature['id'];
//                        $r->value_id = $v->id;
//                        $r->is_sub = $feature['is_sub'] ?? false;
//                        $r->save();
//                    }
//                }
//            }
//        } catch (\Exception $e){
//            dd($e->getMessage());
//        }
//    }
//
//    private function removeFeature(Report $report)
//    {
//        if($report->features()->exists()){
//            $report->features()->with(['value'])->get()->each(function ($model){
//                $model->value->delete();
//            });
//        }
//    }
//
//    private function removeFeatures($reportId)
//    {
//        ReportFeaturePivot::where('report_id', $reportId)->delete();
//    }
//
//
//    private function createSignatureFromBytes($byteStr, $reportId)
//    {
//        if(empty($byteStr)){
//            throw new \Exception('byteData empty for signature');
//        }
//
//        if(!is_string($byteStr)){
//            throw new \Exception('byteData must be string');
//        }
//        $pathStorage = $this->getStoragePath();
//        $basename = Image::SIGNATURE . '.png';
//        $filename = "{$pathStorage}report/{$reportId}/$basename";
//
//        $byteStr = StrHelper::clear($byteStr, ['"' => '', '[' => '', ']' => '']);
//
//        // разбиваем строку
//        $byte = explode(',', $byteStr);
//        // преобразовываем байты
//        array_walk($byte, function (&$item)
//        {
//            $item = chr($item);
//        });
//
//        if(!file_exists("{$pathStorage}report/{$reportId}")){
//            mkdir("{$pathStorage}report/{$reportId}", 0777, true);
//        }
//
//        // создаем подпись
//        file_put_contents($filename, $byte);
//
//        // записываем в бд
//        Image::create(
//            Image::SIGNATURE, Report::class, $reportId, "report/{$reportId}/{$basename}", $basename
//        );
//    }
//
//
//    public function deleteReport(Report $report)
//    {
//        // удаляем pdf
//        if($this->existPdfFile($report->title)){
//            $this->deletePdfFile($report->title);
//        }
//        // удаляем медиа файлы
//        if($this->existMediaFileReport($report->id)){
//            $this->deleteMediaFileReport($report->id);
//        }
//        // удаляем видео
//        if($this->existVideoReport($report->id)){
//            $this->deleteVideoReport($report->id);
//        }
//
//        $report->delete();
//    }
//
//    public function attachVideo(AttachVideoRequest $request, Report $report): Report
//    {
//        if($report->video){
//            $report->video()->delete();
//
//            if($this->existVideoReport($report->id)){
//                $this->deleteVideoReport($report->id);
//            }
//        }
//
//        $video = $request['video'];
//
//        $base = $video->getClientOriginalName();
//
//        $url = Storage::disk('public')
//            ->putFileAs('video/'.$report->id, $video, $base);
//
//        $model = new Video();
//        $model->report_id = $report->id;
//        $model->url = env('APP_URL') . '/storage/'. $url;
//        $model->name = current(explode('.', $base));
//        $model->save();
//
//        return $report->refresh();
//    }
//}
