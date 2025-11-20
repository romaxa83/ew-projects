<?php

namespace App\Resources\Custom;

use App\Helpers\Logger\ReportLogger;
use App\Models\Image;
use App\Models\JD\Client;
use App\Models\Report\Report;
use App\Models\Report\ReportClient;
use App\Models\Report\ReportMachine;
use App\Models\Translate;
use App\Repositories\PageRepository;
use App\Repositories\TranslationRepository;
use App\Type\ClientType;

class CustomReportPdfResource
{
    private $translationRepository;

    public function __construct()
    {
        $this->translationRepository = resolve(TranslationRepository::class);
    }

    public function fill(Report $report): array
    {
        $lang = $report->user->lang ?? \App::getLocale();
        \App::setLocale($lang);

        $disclaimer = app(PageRepository::class)->disclaimerCurrentLocale();
        $translates = $this->translationRepository->listByAliases(Translate::listAliasesForPdfFile(), $lang);

        $first_name = $report->user->profile->first_name ?? '';
        $last_name = $report->user->profile->last_name ?? '';

        $data = [];
        // report main
        $data['title'] = $report->title ?? null;
        $data['salesman_name'] = $report->salesman_name ?? null;
        $data['assignment'] = $report->assignment ?? null;
        $data['client_comment'] = $report->client_comment ?? null;
        $data['demo_result'] = $report->result ?? null;
        // user
        $data['user_full_name'] = ucfirst($first_name) . ' ' . ucfirst($last_name);
        $data['user_country'] = $report->user->country->name ?? null;
//        $data['user_country'] = $report->user->profile->country ?? null;
        $data['user_email'] = $report->user->email ?? null;
        $data['user_phone'] = $report->user->phone ?? null;
        // dealer
        $data['dealer_name'] = $report->user->dealer->name ?? null;
        $data['dealer_country'] = $report->user->dealer->country ?? null;
        $data['dealer_id'] = $report->user->dealer->jd_jd_id ?? null;
        // disclaimer
        $data['disclaimer'] = $disclaimer->current->text ?? null;
        $data['disclaimerTitle'] = $disclaimer->current->name ?? null;
        // translates
        foreach (Translate::listAliasesForPdfFile() as $tran){
            if($tran == 'whatb'){
                $data['translates']['working_hours_at_the_beg'] = $translates[$tran] ?? $tran;
            } elseif ($tran == 'whate') {
                $data['translates']['working_hours_at_the_end'] = $translates[$tran] ?? $tran;
            } elseif ($tran == 'eotf') {
                $data['translates']['equipment_on_the_field'] = $translates[$tran] ?? $tran;
            } elseif ($tran == 'mam') {
                $data['translates']['me_and_equipment'] = $translates[$tran] ?? $tran;
            } elseif ($tran == 'images_others') {
                $data['translates']['others'] = $translates[$tran] ?? $tran;
            } elseif ($tran == 'quantity') {
                $data['translates']['quantity_machine'] = $translates[$tran] ?? 'quantity_machine';
            } else {
                $data['translates'][$tran] = $translates[$tran] ?? $tran;
            }
        }

        // locations
        $data['location'] = '';
        if($report->location){
            $data['location'] .=  $report->location->country ? $report->location->country . ',' : '';
            $data['location'] .=  $report->location->region ? $report->location->region. ',' : '';
            $data['location'] .=  $report->location->city ? $report->location->city. ',' : '';
            $data['location'] .=  $report->location->street ? $report->location->street. ',' : '';
        }
        $data['location_lat'] = $report->location->lat ?? null;
        $data['location_long'] = $report->location->long ?? null;

        // customer
        $customerCount = 0;
        if($report->clients->isNotEmpty()){
            foreach ($report->clients as $item){
                /** @var $item Client*/
                $data['customers'][$customerCount]['company_name'] = $item->company_name;
                $data['customers'][$customerCount]['first_name'] = $item->customer_first_name;
                $data['customers'][$customerCount]['last_name'] = $item->customer_last_name;
                $data['customers'][$customerCount]['phone'] = $item->phone;
                $data['customers'][$customerCount]['product_name'] = $item->modelDescriptionName();
                $data['customers'][$customerCount]['quantity_machine'] = $item->pivot->quantity_machine;
                $data['customers'][$customerCount]['type'] = $item->pivot->type == ClientType::TYPE_POTENTIAL
                    ? $data['translates']['potencial']
                    : $data['translates']['competitor'];

                $customerCount++;
            }
        }
        if($report->reportClients->isNotEmpty()){
            foreach ($report->reportClients as $item){
                /** @var $item ReportClient*/
                $data['customers'][$customerCount]['company_name'] = $item->company_name;
                $data['customers'][$customerCount]['first_name'] = $item->customer_first_name;
                $data['customers'][$customerCount]['last_name'] = $item->customer_last_name;
                $data['customers'][$customerCount]['phone'] = $item->phone;
                $data['customers'][$customerCount]['product_name'] = $item->modelDescriptionName();
                $data['customers'][$customerCount]['quantity_machine'] = $item->pivot->quantity_machine;
                $data['customers'][$customerCount]['type'] =$item->pivot->type == ClientType::TYPE_POTENTIAL
                    ? $data['translates']['potencial']
                    : $data['translates']['competitor'];

                $customerCount++;
            }
        }

        // machine
        if($report->reportMachines->isNotEmpty()){
            foreach ($report->reportMachines as $key => $item){

                /** @var $item ReportMachine*/
                $data['machines'][$key]['manufacturer'] = $item->manufacturer->name ?? null;
                $data['machines'][$key]['equipment_group'] = $item->equipmentGroup->name ?? null;
                $data['machines'][$key]['model_description'] = $item->modelDescription->name ?? null;
                $data['machines'][$key]['model_description.size'] = $item->modelDescription->product->size_name ?? null;
                $data['machines'][$key]['model_description.size_parameter'] = $item->modelDescription->product->sizeParameter->name ?? null;
                $data['machines'][$key]['model_description.type'] = $item->modelDescription->product->type ?? null;
                $data['machines'][$key]['machine_serial_number'] = $item->machine_serial_number;
                $data['machines'][$key]['header_brand'] = $item->headerBrand->name ?? null;
                $data['machines'][$key]['header_model'] = $item->headerModel->name ?? null;
                $data['machines'][$key]['serial_number_header'] = $item->serial_number_header;
                $data['machines'][$key]['sub_manufacturer'] = $item->subManufacturer->name ?? null;
                $data['machines'][$key]['sub_equipment_group'] = $item->subEquipmentGroup->name ?? null;
                $data['machines'][$key]['sub_model_description'] = $item->subModelDescription->name ?? null;
                $data['machines'][$key]['sub_model_description.size'] = $item->subModelDescription->product->size_name ?? null;
                $data['machines'][$key]['sub_model_description.size_parameter'] = $item->subModelDescription->product->sizeParameter->name ?? null;
                $data['machines'][$key]['sub_model_description.type'] = $item->subModelDescription->product->type ?? null;
                $data['machines'][$key]['sub_machine_serial_number'] = $item->sub_machine_serial_number;
                $data['machines'][$key]['trailer_model'] = $item->trailer_model;
                $data['machines'][$key]['type'] = $item->trailed_equipment_type == ReportMachine::INDEPENDENT_MACHINE
                    ? $data['translates']['independent_equipment']
                    : $data['translates']['machine_with_trailer'];
            }
        }

        // feature
        if($report->features && $report->features->isNotEmpty()){
            $features = app(CustomReportFeatureValueResource::class)->fill($report->features, false);
            $data['features'] = array_values($features);
        }

        // image
        if($report->images && $report->images->isNotEmpty()){
            foreach($report->images as $key => $image){
                if($image->model == Image::WORKING_START){
                    $data['images'][Image::WORKING_START][$key] = Image::getUrl($image->url);
                }
                if($image->model == Image::WORKING_END){
                    $data['images'][Image::WORKING_END][$key] = Image::getUrl($image->url);
                }
                if($image->model == Image::EQUIPMENT){
                    $data['images'][Image::EQUIPMENT][$key] = Image::getUrl($image->url);
                }
                if($image->model == Image::ME){
                    $data['images'][Image::ME][$key] = Image::getUrl($image->url);
                }
                if($image->model == Image::OTHERS){
                    $data['images'][Image::OTHERS][$key] = Image::getUrl($image->url);
                }
                if($image->model == Image::SIGNATURE){
                    $data['images'][Image::SIGNATURE] = Image::getUrl($image->url);
                }
            }
        }

        // video
        if($report->video){
            $data['video'] = config('app.url') . "/api/report/download-video/{$report->id}";
        }

        ReportLogger::INFO("DATA FOR PDF [{$report->id}]", $data);

        return $data;
    }
}
