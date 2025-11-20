<?php

namespace App\Exports;

use App\Helpers\DateFormat;
use App\Models\Report\Feature\Feature;
use App\Repositories\Feature\FeatureRepository;
use App\Repositories\TranslationRepository;
use App\Resources\Custom\CustomReportFeatureValueResource;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use \Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ReportExport implements FromCollection,
    WithMapping,
    WithHeadings,
    ShouldAutoSize,
    WithHeadingRow,
    WithCustomStartCell,
    WithDrawings,
    WithEvents
{
    private $data;

    private $countRowIntoData = [];

    private $translationRepository;
    private $translationForExcel;
    private $translationAnother;

    private $featureRepository;

    private $countMainColumn = 0;
    private $countFeatureGroundColumn = 0;
    private $countFeatureMachineColumn = 0;
    private $countFeatureSubMachineColumn = 0;

    // массив id все характеристик по полю
    private $featureGroundIds = [];
    // массив id все характеристик по техники
    private $featureMachineIds = [];

    public function __construct($data)
    {
        $this->data = $data;

        $this->translationRepository = app(TranslationRepository::class);
        $this->translationForExcel = $this->translationRepository->getTranslationForExcel();

        $this->translationAnother = $this->translationRepository->listByAliases([
            'potencial',
            'competitor',
            'Yes',
            'No'
        ]);

        $this->featureRepository = app(FeatureRepository::class);
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($report): array
    {
        return $this->getMatrixData($report);
    }

    public function prettyPhone($phone): string
    {
        return "'" . (string)str_replace('+', '', $phone);
    }

    /**
     * формируем матрицу данных, для вывода дополнительный
     * данных(клиенты, техника) с новой строки
     * приблизительно такого формата
     * arr = [
     *      [name, email, client_name_1, client_status_1],
     *      [null, null, client_name_2, client_status_2],
     *      [null, null, client_name_3, client_status_3],
     * ]
     */
    private function getMatrixData($report)
    {
//        $report->load(['clients.modelDescription', 'reportClients.modelDescription']);
//        dd($report->load(['clients.modelDescription', 'reportClients.modelDescription']));
        // обьединяем коллекции клиентов
        $clients = $report->clients->concat($report->reportClients);
//dd($clients);

//        $allItems = new \Illuminate\Database\Eloquent\Collection;
//        $allItems = $allItems->concat($report->clients);
//        $allItems = $allItems->concat($report->reportClients);
//dd($report->clients, $report->reportClients);
//dd($allItems);

//        $clients = $report->clients->merge($report->reportClients);
//        $clients = array_merge_recursive($report->clients->load(), $report->reportClients);

//dd($report->clients->toArray());
//dd($clients, $report->clients->toArray(),$report->reportClients->toArray());
        $arr = [];
//dd($clients);
        // формируем основные данные (для первой строке)
        $mainData = [
            $report->id,
            $report->title,
            $report->user->dealer->name ?? null,
            $report->user->dealer->country ?? null,
            $report->salesman_name,
            $this->forName(' ',$clients[0]->customer_first_name ?? null, $clients[0]->customer_last_name ?? null, $clients[0]->customer_second_name ?? null),
            $clients[0]->company_name ?? null,
            isset($clients[0]->phone) ? $this->prettyPhone($clients[0]->phone) : null,
            $this->clientStatus($clients[0]->pivot->type ?? null),
            isset($clients[0]) ? $clients[0]->modelDescriptionName() : null,
            $clients[0]->pivot->quantity_machine ?? null,
            $report->reportMachines[0]->manufacturer->name ?? null,
            $report->reportMachines[0]->EquipmentGroup->name ?? null,
            $report->reportMachines[0]->modelDescription->name ?? null,
            $report->reportMachines[0]->machine_serial_number ?? null,
            $report->reportMachines[0]->trailer_model ?? null,
            $report->reportMachines[0]->subManufacturer->name ?? null,
            $report->reportMachines[0]->subEquipmentGroup->name ?? null,
            $report->reportMachines[0]->subModelDescription->name ?? null,
            $report->reportMachines[0]->sub_machine_serial_number ?? null,
            $this->forName(' ', $report->user->profile->first_name ?? null, $report->user->profile->last_name ?? null),
            $report->user->login ?? null,
            $report->user->email ?? null,
            isset($report->user->phone) ? $this->prettyPhone($report->user->phone) : null,
            $report->user->profile->country ?? null,
            DateFormat::front($report->created_at),
            $this->forName(', ', $report->location->country ?? null, $report->location->city ?? null, $report->location->region ?? null, $report->location->street ?? null, $report->location->zipcode ?? null),
            $report->assignment,
            $report->result,
            $report->client_comment
        ];

        // заполняем данные по полю
        $featureGroundTemp = [];

        foreach($report->features as $feature){
            $featureGroundTemp[$feature->feature_id] = $feature->value->value;
        }
        foreach ($this->featureGroundIds as $id){
            if(array_key_exists($id, $featureGroundTemp)){
                $mainData[] = $featureGroundTemp[$id];
            } else {
                $mainData[] = null;
            }
        }

        // сколько эл. уже есть c учетом основных данных, и параметры поля
        $mainDataCount = count($mainData);


        // ЗДЕСЬ ФОРМИРУЕМ ДАННЫЕ ПО ПАРАМЕТРАМ ТЕХНИКИ
        $features = \App::make(CustomReportFeatureValueResource::class)->fill($report->features, false);

        // в $tempMachines отсеиваем показатели по гл. моделям
        $tempMachines = [];
        $tempSubMachines = [];
        foreach ($features ?? [] as $item){
            if(!$item['is_sub'] && $item['type'] == Feature::TYPE_MACHINE){
                $tempMachines[$item['id']] = $item['group'];
            }
            if($item['is_sub'] && $item['type'] == Feature::TYPE_MACHINE){
                $tempSubMachines[$item['id']] = $item['group'];
            }
        }

        // в колонку прописываем название модели техники
        if(!empty($tempMachines)){
            $mainData[] = current(current($tempMachines))['name'] ?? null;
        }
        // в $countLost записываем все кол-во оставшихся показателей сравниваемой техники
        $countLost[] = 0;
        foreach ($this->featureMachineIds as $id){
            if(array_key_exists($id, $tempMachines)){
                // вырезаем первый эл. (это основная техника), и добавляем к основным данным
                $mainData[] = $this->prettyValue(array_shift($tempMachines[$id])['value']);
                $countLost[] = count($tempMachines[$id]);
            } else {
                $mainData[] = null;
            }
        }
        $countLost = max($countLost);
        $mainDataCountAfterMachine = count($mainData);

        // в колонку прописываем название модели прицепной техники
        if(!empty($tempSubMachines)){
            $mainData[] = current(current($tempSubMachines))['name'] ?? null;
        }
        // в $countLost записываем все кол-во оставшихся показателей сравниваемой техники
        $countLostSub[] = 0;
        foreach ($this->featureMachineIds as $id){
            if(array_key_exists($id, $tempSubMachines)){
                // вырезаем первый эл. (это основная прицепная техника), и добавляем к основным данным
                $mainData[] = $this->prettyValue(array_shift($tempSubMachines[$id])['value']);
                $countLostSub[] = count($tempSubMachines[$id]);
            } else {
                $mainData[] = null;
            }
        }

        $countLostSub = max($countLostSub);

        array_push($arr,$mainData);
        // формируем дополнительный данные (следующие строки) для клиентов
        if($clients->count() > 1){
            foreach ($clients as $key => $client){
                // пропускаем первые данные ,т.к. они выведены выше
                if($key != 0){
                    $arrClient = $this->fillNullArray(5);
                    $arrClient[] = $this->forName( ' ',$client->customer_first_name, $client->customer_last_name, isset($client->customer_second_name) ? $client->customer_second_name : null);
                    $arrClient[] = $client->company_name;
                    $arrClient[] = (string)$client->phone;
                    $arrClient[] = $this->clientStatus($client->pivot->type);
                    array_push($arr, $arrClient);
                }
            }
        }

        // формируем дополнительный данные (следующие строки) для техники
        if($report->reportMachines->count() > 1){
            foreach ($report->reportMachines as $key => $machine){
                if($key != 0){
                    // если строка уже есть с данными(клиента), добавляем данные по технике
                    if(isset($arr[$key])){

                        $arrMachine = $this->fillNullArray(11, $arr[$key]);

                        $arrMachine[] = $machine->manufacturer->name ?? null;
                        $arrMachine[] = $machine->equipment_group_id ? $machine->equipmentGroup->name : $machine->equipment_group;
                        $arrMachine[] = $machine->model_description_id ? $machine->modelDescription->name : $machine->model_description;
                        $arrMachine[] = $machine->machine_serial_number;
                        $arrMachine[] = $machine->trailer_model;
                        $arrMachine[] = $machine->subManufacturer->name ?? null;
                        $arrMachine[] = $machine->subEquipmentGroup->name ?? null;
                        $arrMachine[] = $machine->subModelDescription->name ?? null;
                        $arrMachine[] = $machine->sub_machine_serial_number;

                        $arr[$key] = $arrMachine;
                    } else {
                        // формируем новую строку
                        $arrMachine = $this->fillNullArray(11);

                        $arrMachine[] = $machine->manufacturer->name ?? null;
                        $arrMachine[] = $machine->equipment_group_id ? $machine->equipmentGroup->name : $machine->equipment_group;
                        $arrMachine[] = $machine->model_description_id ? $machine->modelDescription->name : $machine->model_description;
                        $arrMachine[] = $machine->machine_serial_number;
                        $arrMachine[] = $machine->trailer_model;
                        $arrMachine[] = $machine->subManufacturer->name ?? null;
                        $arrMachine[] = $machine->subEquipmentGroup->name ?? null;
                        $arrMachine[] = $machine->subModelDescription->name ?? null;
                        $arrMachine[] = $machine->sub_machine_serial_number;

                        array_push($arr, $arrMachine);
                    }
                }
            }
        }

        // формируем данные по "техники для сравнения", если така есть
        for($i = 0; $i < $countLost; $i++){
            // заполняем новые строки null
            $lostMachineFeatures = $this->fillNullArray($mainDataCount);
            // в колонку прописываем название модели техники
            $lostMachineFeatures[] = current(current($tempMachines))['name'] ?? null;
            foreach ($this->featureMachineIds as $id) {
                if(array_key_exists($id, $tempMachines)){
                    $lostMachineFeatures[] = $this->prettyValue(array_shift($tempMachines[$id])['value'] ?? null);
                } else {
                    $lostMachineFeatures[] = null;
                }
            }
            array_push($arr,$lostMachineFeatures);
        }

        // формируем данные по "прицепной техники техники для сравнения", если така есть
        for($i = 0; $i < $countLostSub; $i++){
              $j = $i + 1;
            if(isset($arr[$j])){
                $lostMachineSubFeatures = $this->fillNullArray($mainDataCountAfterMachine, $arr[$j]);
                $lostMachineSubFeatures[] = current(current($tempSubMachines))['name'] ?? null;
                foreach ($this->featureMachineIds as $id) {

                    if(array_key_exists($id, $tempSubMachines)){
                        $lostMachineSubFeatures[] = $this->prettyValue(array_shift($tempSubMachines[$id])['value'] ?? null);
                    } else {
                        $lostMachineSubFeatures[] = null;
                    }
                }
                $arr[$j] = $lostMachineSubFeatures;
            } else {
                $lostMachineFeatures = $this->fillNullArray($mainDataCountAfterMachine);
                foreach ($this->featureMachineIds as $id) {
                    if(array_key_exists($id, $tempSubMachines)){
                        $lostMachineSubFeatures[] = array_shift($tempSubMachines[$id])['value'];
                    } else {
                        $lostMachineSubFeatures[] = null;
                    }
                }
                array_push($arr,$lostMachineFeatures);
            }
        }

        // добавляем кол-во доп. строка для одного отчета,для добавления подчеркивания чтоб разделить отчеты
        array_push($this->countRowIntoData, count($arr));
//        dd($arr);
        return $arr;
    }

    public function prettyValue($value)
    {
        if(is_bool($value)){
            $value = false === $value
                ? $this->translationAnother['No'] ?? 'No'
                : $this->translationAnother['Yes'] ?? 'Yes';
        }

        return $value;
    }

    // наполняем массив null определенного кол-ва
    private function fillNullArray($count, $arr = [])
    {
        for($i = 0; $i < $count; $i++){
            $arr[$i] = isset($arr[$i]) ? $arr[$i] : null;
        }

        return $arr;
    }

    // склеиваем данные
    private function forName($glue, ...$data)
    {
        return implode($glue, $data);
    }

    private function clientStatus($status)
    {
        if(null === $status){
            return '';
        }
        return $status
            ? $this->translationAnother['potencial'] ?? 'potencial'
            : $this->translationAnother['competitor'] ?? 'competitor';
    }

    public function headings(): array
    {
        $mainField = [
            $this->translationForExcel['file.id'] ?? null,
            $this->translationForExcel['file.title'] ?? null,
            $this->translationForExcel['file.dealer_name'] ?? null,
            $this->translationForExcel['file.dealer_company'] ?? null,
            $this->translationForExcel['file.salesman_name'] ?? null,
            $this->translationForExcel['file.client_name'] ?? null,
            $this->translationForExcel['file.client_company'] ?? null,
            $this->translationForExcel['file.client_phone'] ?? null,
            $this->translationForExcel['file.client_status'] ?? null,
            $this->translationForExcel['file.client_machine'] ?? null,
            $this->translationForExcel['file.client_quantity_machine'] ?? null,
            $this->translationForExcel['file.manufacturer'] ?? null,
            $this->translationForExcel['file.equipment_group'] ?? null,
            $this->translationForExcel['file.model_description'] ?? null,
            $this->translationForExcel['file.machine_serial_number'] ?? null,
            $this->translationForExcel['file.trailer_model'] ?? null,
            $this->translationForExcel['file.sub_manufacture'] ?? null,
            $this->translationForExcel['file.sub_equipment_group'] ?? null,
            $this->translationForExcel['file.sub_model_description'] ?? null,
            $this->translationForExcel['file.sub_serial_number'] ?? null,
            $this->translationForExcel['file.ps_name'] ?? null,
            $this->translationForExcel['file.ps_login'] ?? null,
            $this->translationForExcel['file.ps_email'] ?? null,
            $this->translationForExcel['file.ps_phone'] ?? null,
            $this->translationForExcel['file.ps_country'] ?? null,
            $this->translationForExcel['file.created'] ?? null,
            $this->translationForExcel['file.location'] ?? null,
            $this->translationForExcel['file.assignment'] ?? null,
            $this->translationForExcel['file.result'] ?? null,
            $this->translationForExcel['file.client_comment'] ?? null,
        ];
        $this->countMainColumn = count($mainField);

        $featuresGround = $this->featureRepository
            ->getAll(['current'], ['type' => Feature::TYPE_GROUND],['position' =>'desc'], true)
            ->map(function(Feature $m){
                $name = $m->current->name;
                $this->featureGroundIds[] = $m->id;
                if($m->current->unit){
                    $name .= ' ( '. $m->current->unit .' )';
                }

                return $name;
            })
            ->toArray();

        $this->countFeatureGroundColumn = count($featuresGround);

        $featuresMachine = [];
        $featuresSubMachine = [$this->translationForExcel['file.head_sub_model'] ?? null];
        $featuresMachine[] = $this->translationForExcel['file.head_model'] ?? null;
        $this->featureRepository
            ->getAll(['current'], ['type' => Feature::TYPE_MACHINE],['position' =>'desc'], true)
            ->map(function(Feature $m) use (&$featuresMachine, &$featuresSubMachine) {
                $name = $m->current->name;
                $this->featureMachineIds[] = $m->id;
                if($m->current->unit){
                    $name .= ' ( '. $m->current->unit .' )';
                }

                $featuresMachine[] = $name;
                $featuresSubMachine[] = $name;
            })
            ->toArray();
        $this->countFeatureMachineColumn = count($featuresMachine);

        $data = array_merge($mainField, $featuresGround, $featuresMachine, $featuresSubMachine);

        return $data;
    }

    /*
     * @see https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
     */
    public function registerEvents(): array
    {
        $styleFont = [
            'font' => [
               'bold' => true
            ]
        ];

        $styleBorder = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
        ];

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        $redBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => 'FFFF0000']
                ]
            ]
        ];

        $fillGreen = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'startColor' => [
                    'argb' => '7ad378',
                ],
                'endColor' => [
                    'argb' => '7ad378',
                ],

            ],
        ];
        $fillBlue = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => '1da1f2',
                ],
                'endColor' => [
                    'argb' => '1da1f2',
                ],
            ],
        ];
        $fillRed = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'ff4646',
                ],
                'endColor' => [
                    'argb' => 'ff4646',
                ],
            ],
        ];
        $fillYellow = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'ffe496',
                ],
                'endColor' => [
                    'argb' => 'ffe496',
                ],
            ],
        ];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($styleFont, $styleBorder, $styleArray, $fillBlue, $fillGreen, $fillRed, $fillYellow){
                /** @var Sheet $sheet */
                $sheet = $event->sheet;

                // фиксируем размер столбцов для длинных строк
                $sheet->getColumnDimension('B')->setAutoSize(false);
                $sheet->getColumnDimension('G')->setAutoSize(false);
                $sheet->getColumnDimension('AB')->setAutoSize(false);
                $sheet->getColumnDimension('AC')->setAutoSize(false);
                $sheet->getColumnDimension('AD')->setAutoSize(false);
                $sheet->getColumnDimension('B')->setWidth(40.5);
                $sheet->getColumnDimension('G')->setWidth(40.5);
                $sheet->getColumnDimension('AB')->setWidth(30.5);
                $sheet->getColumnDimension('AC')->setWidth(20.5);
                $sheet->getColumnDimension('AD')->setWidth(30.5);

                // формируем хедеры для основных данных отчета
                $sheet->mergeCells("A1:{$this->getExcelColumn($this->countMainColumn)}1");
                $sheet->setCellValue('A1', $this->translationForExcel['file.head_data_report'] ?? null);
                $sheet->getDelegate()->getStyle('A1')->applyFromArray($fillGreen);

                // формируем хедеры для данных по полю
                $startGroundFeature = $this->countMainColumn + 1;
                $endGroundFeature = $this->countMainColumn + $this->countFeatureGroundColumn;

                $sheet->mergeCells("{$this->getExcelColumn($startGroundFeature)}1:{$this->getExcelColumn($endGroundFeature)}1");
                $sheet->setCellValue("{$this->getExcelColumn($startGroundFeature)}1", $this->translationForExcel['file.head_feature_ground_report'] ?? null);
                $sheet->getDelegate()->getStyle("{$this->getExcelColumn($startGroundFeature)}1")->applyFromArray($fillBlue);

                // формируем хедеры для данных по машине
                $startMachineFeature = $endGroundFeature + 1;
                $endMachineFeature = $endGroundFeature + $this->countFeatureMachineColumn;

                $sheet->mergeCells("{$this->getExcelColumn($startMachineFeature)}1:{$this->getExcelColumn($endMachineFeature)}1");
                $sheet->setCellValue("{$this->getExcelColumn($startMachineFeature)}1", $this->translationForExcel['file.head_feature_machine_report'] ?? null);
                $sheet->getDelegate()->getStyle("{$this->getExcelColumn($startMachineFeature)}1")->applyFromArray($fillRed);

                // формируем хедеры для прицепной техники
                $startSubMachineFeature = $endMachineFeature + 1;
                $endSubMachineFeature = $endMachineFeature + $this->countFeatureMachineColumn;

                $sheet->mergeCells("{$this->getExcelColumn($startSubMachineFeature)}1:{$this->getExcelColumn($endSubMachineFeature)}1");
                $sheet->setCellValue("{$this->getExcelColumn($startSubMachineFeature)}1", $this->translationForExcel['file.head_feature_sub_machine_report'] ?? null);
                $sheet->getDelegate()->getStyle("{$this->getExcelColumn($startSubMachineFeature)}1")->applyFromArray($fillYellow);

                $sheet->getDelegate()->getStyle("A1:{$this->getExcelColumn($endSubMachineFeature)}1")->applyFromArray($styleArray);
                $sheet->getDelegate()->getStyle("A2:{$this->getExcelColumn($endSubMachineFeature)}2")->applyFromArray($styleArray);

                $sheet->getDelegate()->getStyle("A1:{$this->getExcelColumn($endSubMachineFeature)}1")->applyFromArray($styleFont);
                $sheet->getDelegate()->getStyle("A2:{$this->getExcelColumn($endSubMachineFeature)}2")->applyFromArray($styleFont);

                $sheet->getDelegate()->getStyle("A2:{$this->getExcelColumn($endSubMachineFeature)}2")->applyFromArray($styleBorder);

                // разделитель для отчетов (т.к. в одном отчете может быть несколько строк)
                $count = 2;
                foreach ($this->countRowIntoData as $rowCount){
                    $count += $rowCount;
                    $sheet->getDelegate()->getStyle("A{$count}:{$this->getExcelColumn($endSubMachineFeature)}{$count}")->applyFromArray($styleBorder);
                }
            }
        ];
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function acd(): array
    {
        return ['A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I',
            'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X',
            'Y', 'Z',
        ];
    }

    public function getExcelColumn(int $position): string
    {
        if($position <= count($this->acd())){
            return $this->acd()[$position - 1];
        } else {
            $first = (intdiv($position, count($this->acd()))) - 1;
            $second = ($position % count($this->acd()));
            if($second == 0){
                $first -= 1;
                $second = count($this->acd());
            }

            return $this->acd()[$first] . $this->acd()[$second - 1];
        }
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('/static/logo.png'));
        $drawing->setHeight(20);
        $drawing->setCoordinates('B1');

        return $drawing;
    }
}
