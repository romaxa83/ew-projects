<?php

namespace App\Services\Report;

use App\DTO\Report\ClientDto;
use App\DTO\Report\FeatureDto;
use App\DTO\Report\FeatureValueDto;
use App\DTO\Report\ImageDto;
use App\DTO\Report\LocationDto;
use App\DTO\Report\MachineDto;
use App\DTO\Report\ReportDto;
use App\Helpers\DateFormat;
use App\Helpers\ReportHelper;
use App\Helpers\StrHelper;
use App\Models\Image;
use App\Models\Report\Feature\ReportFeatureValue;
use App\Models\Report\Feature\ReportValue;
use App\Models\Report\Location;
use App\Models\Report\Report;
use App\Models\Report\ReportClient;
use App\Models\Report\ReportMachine;
use App\Models\Report\ReportPushData;
use App\Models\Report\Video;
use App\Repositories\JD\ClientRepository;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\Feature\FeatureRepository;
use App\Traits\StoragePath;
use App\Type\ReportStatus;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    use StoragePath;

    public function __construct(
        protected ClientRepository $clientRepository,
        protected EquipmentGroupRepository $equipmentGroupRepository,
        protected FeatureRepository $featuresRepository
    )
    {}

    public function create(ReportDto $dto): ?Report
    {
        return DB::transaction(function() use ($dto) {
            $model = $this->saveReport($dto);
            $dto->setReportID($model->id);

            // запланированная дата, для уведомлений
            $pushData = new ReportPushData();
            if ($dto->plannedAt){
                $pushData->planned_at = DateFormat::timestampMsToDate($dto->plannedAt);
            }
            $model->pushData()->save($pushData);

            $this->saveLocation($dto);

            $nameClient = $this->saveClients($dto, $model);
            $nameModel = $this->saveMachines($dto, $model);

            // сохраняем характеристики
            if ($dto->hasFeatures()){
                $this->saveFeatures($dto);
                if($model->features->isNotEmpty()){
                    $model->fill_table_date = CarbonImmutable::now();
                }
            }

            $this->saveReportImages($dto);

            // создание и сохранения подписи из байтов
            if($dto->hasSignature()){
                $this->createSignatureFromBytes($dto->signature, $dto->getReportID());
            }

            $title = $dto->getUserDealerName() .'_'.$nameClient.'_'.$nameModel.'_'.DateFormat::forTitle($model->created_at);

            $model->title = ReportHelper::prettyTitle($title);
            $model->save();

            return $model;
        });
    }

    public function updatePs(ReportDto $dto, Report $model): Report
    {
        // получаем eq ,чтоб проверить если у него характеристики, т.к. характеристики которые
        // заплняються в отчете, привязанны к eg (у каждой eg свой набор характеристик)
        $eq = false;
        if ($egID = $dto->getEgID()){
            $eq = $this->equipmentGroupRepository->getBy('id', $egID);
        }

        $model->status = $dto->status ?? $model->status;
        $model->salesman_name = $dto->salesmanName ?? $model->salesman_name;
        $model->assignment = $dto->assignment ?? $model->assignment;
        $model->client_comment = $dto->clientComment ?? $model->client_comment;
        $model->client_email = $dto->clientEmail ?? $model->client_email;

        if($eq){
            if($eq->features->isNotEmpty()){
                $model->result = null;
            } else {
                $model->result = $dto->result ?? null;
            }
        }

        if($dto->result){
            $model->result = $dto->result;
        }

        DB::beginTransaction();
        try {
            $model->save();

            // планируемая дата
            if ($dto->plannedAt){
                $plannedAt = DateFormat::timestampMsToDate($dto->plannedAt);
                if(isset($model->pushData)){
                    if($model->pushData->planned_at){
                        // если пришедшая "планируемая" дата не совпадает с уже существуещей, то перезаписуем
                        if(!$model->pushData->equalsPlannedDate($plannedAt)){
                            $oldDate = $model->pushData->planned_at;
                            $model->pushData->prev_planned_at = $oldDate;
                            $model->pushData->planned_at = $plannedAt;
                            $model->pushData->is_send_start_day = false;
                            $model->pushData->is_send_end_day = false;
                            $model->pushData->is_send_week = false;
                        }
                    } else {
                        $model->pushData->planned_at = $plannedAt;
                    }

                    $model->pushData()->save($model->pushData);
                } else {
                    // создаем данные , актуально для старых отчетов
                    $pushData = new ReportPushData();
                    $pushData->planned_at = $plannedAt;
                    $model->pushData()->save($pushData);
                }
            }

            // сохраняем локацию, если она пришла и ее еще нет у отчета
            if($model->location == null){
                $this->saveLocation($dto);
            }

            $model->clients()->detach($model->clients);
            $model->reportClients()->delete();
            $nameClient = $this->saveClients($dto, $model);

            $model->reportMachines()->delete();
            $nameModel = $this->saveMachines($dto, $model);

            // если изменился eg и у него нет привязаных характеристик, то удаляем их и не привязываем новые
            if($eq){
                if($eq->features->isNotEmpty()){
                    if ($dto->hasFeatures()){
                        // удаляем старые характеристики
                        $this->removeFeature($model);
                        $this->saveFeatures($dto);
                        if(!$model->fill_table_date){
                            $model->fill_table_date = CarbonImmutable::now();
                        }
                    } else {
                        if($model->features){
                            $this->removeFeature($model);
                            $model->fill_table_date = null;
                        }
                    }
                } else {
                    //удаляем старые характеристики
                    $this->removeFeature($model);
                    $model->fill_table_date = null;
                }
            }
            $deleteImg = $this->updateReportImages($dto);

            // создаем подпись только если ее нету
            if($dto->hasSignature() && !$model->hasSignature()){
                $this->createSignatureFromBytes($dto->signature, $dto->getReportID());
            }

            $title = $dto->getUserDealerName() .'_'.$nameClient.'_'.$nameModel.'_'.DateFormat::forTitle($model->created_at);

            $model->title = ReportHelper::prettyTitle($title);
            $model->save();

            DB::commit();
            // удаляем файлы с диска
            $this->deleteFilesByLink($deleteImg);
            $model->refresh();

            return $model;
        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param $request
     * @param Report $report
     * @return Report|null
     * @throws \Exception
     */
    public function update($request, Report $report): ?Report
    {
        $report->load(['clients', 'reportClients', 'reportMachines']);

        $report->title = $request->title ? $request->title : $report->title;
        $report->salesman_name = $request->salesman_name ? $request->salesman_name : $report->salesman_name;
        $report->result = $request->result ? $request->result : $report->result;
        $report->assignment = $request->assignment ? $request->assignment : $report->assignment;
        $report->client_comment = $request->client_comment ? $request->client_comment : $report->client_comment;
        $report->client_email = $request->client_email ? $request->client_email : $report->client_email;

        DB::beginTransaction();

        try {
            foreach($request->machines ?? [] as $machine){
                /** @var $mac ReportMachine */
                $mac = $report->reportMachines->where('id', $machine['id'])->first();

                $mac->header_brand_id = $machine['header_brand_id'] ?? $mac->header_brand_id;
                $mac->header_model_id = $machine['header_model_id'] ?? $mac->header_model_id;
                $mac->serial_number_header = $machine['serial_number_header'] ?? $mac->serial_number_header;
                $mac->machine_serial_number = $machine['machine_serial_number'] ?? $mac->machine_serial_number;
                $mac->trailed_equipment_type = $machine['trailed_equipment_type'] ?? $mac->trailed_equipment_type;
                $mac->trailer_model = $machine['trailer_model'] ?? $mac->trailer_model;
                $mac->model_description_id = $machine['model_description_id'] ?? $mac->model_description_id;
                $mac->equipment_group_id = $machine['equipment_group_id'] ?? $mac->equipment_group_id;

                $mac->save();
            }

            if(isset($request['clients']) && !empty($request['clients'])){
                foreach ($request['clients'] as $clientType => $clients){
                    if($clientType == 'john_dear_client'){
                        foreach ($clients as $key => $client){
                            if(isset($client['quantity_machine'])){
                                $report->clients()
                                    ->where('id', $report->clients[$key]->id)
                                    ->updateExistingPivot($report->clients[$key]->id,[
                                        'quantity_machine' => $client['quantity_machine']
                                    ]);
                            }
                        }
                    }
                    if($clientType == 'report_client'){
                        foreach ($clients as $key => $client){
                            if(isset($client['quantity_machine'])){
                                $report->reportClients()
                                    ->where('id', $report->reportClients[$key]->id)
                                    ->updateExistingPivot($report->reportClients[$key]->id,[
                                        'quantity_machine' => $client['quantity_machine']
                                    ]);
                            }
                        }
                    }
                }
            }

            if($report->comment){
                $report->comment->text = $request->comment;
                $report->comment->save();
            }

            $report->save();

            DB::commit();
            $report->refresh();

            return $report;
        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());
            throw new \Exception($exception->getMessage());
        }
    }

    // @todo удалить обновление поля verify (также удалить из бд)
    public function verify(Report $report): Report
    {
        if(!ReportStatus::canToggleToVerify($report->status)){
            throw new \Exception(__('message.cannot toggle report to verify status'));
        }

        $report->verify = true;
        $this->changeStatus($report, ReportStatus::VERIFY);

        $report->save();

        return $report;
    }

    public function changeStatus(Report $report, $status): Report
    {
        $report->status = $status;
        $report->save();

        return $report;
    }

    private function saveReport(ReportDto $dto): Report
    {
        $model = new Report();
        $model->status = $dto->status;
        $model->user_id = $dto->getUserID();
        $model->salesman_name = $dto->salesmanName;
        $model->assignment = $dto->assignment;
        $model->result = $dto->result;
        $model->client_comment = $dto->clientComment;
        $model->client_email = $dto->clientEmail;

        $model->save();

        return $model;
    }

    private function saveLocation(ReportDto $dto): void
    {
        if($location = $dto->location){
            /** @var $location LocationDto */
            $model = new Location();
            $model->report_id = $dto->getReportID();
            $model->lat = $location->lat;
            $model->long = $location->long;
            $model->country = $location->country;
            $model->city = $location->city;
            $model->region = $location->region;
            $model->zipcode = $location->zipcode;
            $model->street = $location->street;
            $model->district = $location->district;

            $model->save();
        }
    }

    private function saveReportClient(ClientDto $dto): ReportClient
    {
        $model = new ReportClient();
        $model->customer_id = $dto->customerID;
        $model->customer_first_name = $dto->firstName;
        $model->customer_last_name = $dto->lastName;
        $model->company_name = $dto->companyName;
        $model->phone = $dto->phone;
        $model->comment = $dto->comment;

        $model->save();

        return $model;
    }

    private function saveClients (ReportDto $dto, Report $model): ?string
    {

        $name= null ;
        foreach($dto->getClients() ?? [] as $client){
            /** @var $client ClientDto */
            if($client->isJDClient()){
                // привязываем клиента из jd1
                $cli = $this->clientRepository->getBy('id', $client->clientID);
                $attachClient = $model->clients();
            } else {
                // создаем клиента для отчета
                $cli = $this->saveReportClient($client);
                $attachClient = $model->reportClients();
            }
            $attachClient->attach($cli,[
                'type' => $client->type,
                'model_description_id' => $client->mdID,
                'quantity_machine' => $client->quantityMachine
            ]);

            if($name == null){
                $name = $cli->company_name;
            }
        }

        return $name;
    }

    private function saveMachines (ReportDto $dto, Report $model): ?string
    {
        $name = null ;
        foreach($dto->getMachines() ?? [] as $machine){
            /** @var $machine MachineDto */
            $mac = $this->saveMachine($machine);
            $model->reportMachines()->attach($mac);

            if($name == null){
                $name = $mac->modelDescription ? $mac->modelDescription->name : null;
            }
        }

        return $name;
    }

    private function saveMachine(MachineDto $dto): ReportMachine
    {
        $model = new ReportMachine();
        $model->manufacturer_id = $dto->manufacturerID;
        $model->equipment_group_id = $dto->egID;
        $model->model_description_id = $dto->mdID;
        $model->trailed_equipment_type = $dto->trailedEquipmentType;
        $model->trailer_model = $dto->trailerModel;
        $model->header_brand_id = $dto->headerBrandID;
        $model->header_model_id = $dto->headerModelID;
        $model->serial_number_header = $dto->serialNumberHeader;
        $model->machine_serial_number = $dto->machineSerialNumber;
        $model->sub_machine_serial_number = $dto->subMachineSerialNumber;
        $model->sub_equipment_group_id = $dto->subEgID;
        $model->sub_model_description_id = $dto->subMdID;
        $model->sub_manufacturer_id = $dto->subManufacturerID;

        $model->save();

        return $model;
    }

    private function saveReportImages(ReportDto $dto)
    {
        foreach ($dto->getImages() ?? [] as $imageDto){
            /** @var $imageDto ImageDto */
            foreach($imageDto->images ?? [] as $image){
                $this->saveOneImage($image, $dto->getReportID(), $imageDto->module);
            }
        }
    }

    private function updateReportImages(ReportDto $dto): array
    {
        $deleteFile = [];
        $existFile = [];

        foreach ($dto->getImages() ?? [] as $imageDto) {
            if($imageDto->emptyImages()){
                $existFile[$imageDto->module] = [];
            }
            /** @var $imageDto ImageDto */
            foreach ($imageDto->images ?? [] as $key => $image) {
                if ($image instanceof UploadedFile) {
                    $existFile[$imageDto->module][$key] = $this->saveOneImage($image, $dto->getReportID(), $imageDto->module);
                }

                if (is_string($image)) {
                    $existFile[$imageDto->module][$key] = $image;
                }

            }
        }
        // удаляем файлы
        foreach ($existFile ?? [] as $moduleName => $urlFile){
            $urlFiles = [];
            foreach($urlFile ?? [] as $key => $item){
                $urlFiles[$key] = last(explode('/', $item));
            }

            $models = Image::query()
                ->where('model', $moduleName)
                ->where('entity_id', $dto->getReportID())
                ->whereNotIn('basename', array_values($urlFiles))
                ->where('model', '!=', Image::SIGNATURE)
                ->get();

            foreach ($models as $model){
                $deleteFile[] = $model->url;
                $model->forceDelete();
            }
        }

        return $deleteFile;
    }

    private function saveOneImage($file, $reportId, $key = 'some')
    {
        /** @var $file UploadedFile */
        $basename = $file->getClientOriginalName();

        Storage::disk('public')
            ->putFileAs('report/'.$reportId, $file, $basename);

        Image::create(
            $key, Report::class, $reportId, "report/{$reportId}/{$basename}", $basename, $file
        );

        return $basename;
    }

    public function saveFeatures(ReportDto $dto)
    {
        foreach($dto->getFeatures() ?? [] as $feature){
            /** @var $feature FeatureDto */
            // сохраняем только активированные характеристики
            if($this->featuresRepository->activeFeature($feature->ID)){
                foreach($feature->values ?? [] as $value){
                    /** @var $value FeatureValueDto */
                    $v = new ReportValue();
                    $v->value = $value->value;
                    $v->model_description_id = $value->mdID;
                    $v->model_description_name = $value->mdName;
                    $v->value_id = $value->valueID;
                    $v->save();

                    $r = new ReportFeatureValue();
                    $r->report_id = $dto->getReportID();
                    $r->feature_id = $feature->ID;
                    $r->value_id = $v->id;
                    $r->is_sub = $feature->isSub;
                    $r->save();
                }
            }
        }
    }

    private function removeFeature(Report $report)
    {
        if($report->features()->exists()){
            $report->features()->with(['value'])->get()->each(function ($model){
                $model->value->delete();
            });
        }
    }

    private function createSignatureFromBytes($byteStr, $reportId)
    {
        if(empty($byteStr)){
            throw new \Exception('byteData empty for signature');
        }

        if(!is_string($byteStr)){
            throw new \Exception('byteData must be string');
        }
        $pathStorage = $this->getStoragePath();
        $basename = Image::SIGNATURE . '.png';
        $filename = "{$pathStorage}report/{$reportId}/$basename";

        $byteStr = StrHelper::clear($byteStr, ['"' => '', '[' => '', ']' => '']);

        // разбиваем строку
        $byte = explode(',', $byteStr);
        // преобразовываем байты
        array_walk($byte, function (&$item)
        {
            $item = chr($item);
        });

        if(!file_exists("{$pathStorage}report/{$reportId}")){
            mkdir("{$pathStorage}report/{$reportId}", 0777, true);
        }

        // создаем подпись
        file_put_contents($filename, $byte);

        // записываем в бд
        Image::create(
            Image::SIGNATURE, Report::class, $reportId, "report/{$reportId}/{$basename}", $basename
        );
    }

    public function deleteReport(Report $report)
    {
        // удаляем pdf
        if($this->existPdfFile($report->title)){
            $this->deletePdfFile($report->title);
        }
        // удаляем медиа файлы
        if($this->existMediaFileReport($report->id)){
            $this->deleteMediaFileReport($report->id);
        }
        // удаляем видео
        if($this->existVideoReport($report->id)){
            $this->deleteVideoReport($report->id);
        }

        $report->delete();
    }

    public function attachVideo(UploadedFile $video, Report $report): Report
    {
        if($report->video){
            $report->video()->delete();

            if($this->existVideoReport($report->id)){
                $this->deleteVideoReport($report->id);
            }
        }

        $base = $video->getClientOriginalName();

        $url = Storage::disk('public')
            ->putFileAs('video/'.$report->id, $video, $base);

        $model = new Video();
        $model->report_id = $report->id;
        $model->url = config('app.url') . '/storage/'. $url;
        $model->name = current(explode('.', $base));
        $model->save();

        return $report->refresh();
    }
}
