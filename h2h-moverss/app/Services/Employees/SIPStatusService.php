<?php

namespace App\Services\Employees;

use App\Models\Division;
use App\Models\Employee;
use App\Services\Employees\Entities\RingoEmployeeEntity;
use App\Services\Requests\Ringostat\Commands\GetSipStatusCommand;

class SIPStatusService
{
    public const CUSTOMER_SUPPORT_DEPARTMENT_ID = 11309;
    public const SALES_LOCAL_DEPARTMENT_ID      = 11304;
    public const SALES_LONG_DEPARTMENT_ID       = 11314;
    public const CLAIMS_DEPARTMENT_ID           = 11319;

    private static array $excludeSips = [
        'h2hmoverscom_concierge_ai',
        'h2hmoverscom_john',
        'h2hmoverscom_rebekah',
        'h2hmoverscom_nicole',
        'h2hmoverscom_jackie',
        'h2hmoverscom_victoria',
        'h2hmoverscom_valerie',
        'h2hmoverscom_jena',
        'h2hmoverscom_taylor',
        'h2hmoverscom_roman',
        'h2hmoverscom_mitch',
        'h2hmoverscom_khaye',
        'h2hmoverscom_maria',
        'h2hmoverscom_vitaliy',
        'h2hmoverscom_stella',
        'h2hmoverscom_alex',
        'h2hmoverscom_julian',
        'h2hmoverscom_julian_mob',
        'h2hmoverscom_brian',
        'h2hmoverscom_larry',
        'h2hmoverscom_mark',
        'h2hmoverscom_mark_mob',
    ];


    protected array $data = [];

    private function __construct()
    {
        $configSips = array_filter(
            config('vapi.excludes_sip') ?? [],
            'strlen'
        );

        self::$excludeSips = array_merge(
            self::$excludeSips,
            $configSips
        );

        $this->data = $this->getSipsFromRingostat();
    }

    public static function getOnline(array $departments = []): RingoEmployeeEntity
    {
        $self = new self();

        return $self
            ->filteredAsOnline()
            ->excludeSIPs()
            ->onlyDepartments($departments)
            ->notOnCall()
            ->onlyOne()
            ->getEmployeeEntity()
        ;

    }

    protected function getSipsFromRingostat(): array
    {
        $division = Division::find(1);

        $command = resolve(GetSipStatusCommand::class, ['division' => $division]);
        return $command->exec();
    }

    protected function filteredAsOnline(): self
    {
//        \Log::info('SIPs FROM RINGOSTAT@filteredAsOnline START', [
//            $this->data
//        ]);
        foreach ($this->data as $k => $item) {
            if(!$item['status']){
                unset($this->data[$k]);
            }
        }
//        \Log::info('SIPs FROM RINGOSTAT@filteredAsOnline FINE', [
//            $this->data
//        ]);

        return $this;
    }

    protected function excludeSIPs(): self
    {
//        \Log::info('SIPs FROM RINGOSTAT@excludeSIPs START', [
//            $this->data
//        ]);

        foreach ($this->data as $k => $item) {
            foreach ($item['directions']['main'] ?? [] as $mainDirection){
                if(in_array($mainDirection['direction'], self::$excludeSips)){
                    unset($this->data[$k]);
                    break;
                }
            }
            foreach ($item['directions']['additional'] ?? [] as $mainDirection){
                if(in_array($mainDirection['direction'], self::$excludeSips)){
                    unset($this->data[$k]);
                    break;
                }
            }
        }

//        \Log::info('SIPs FROM RINGOSTAT@excludeSIPs FINE', [
//            $this->data
//        ]);

        return $this;
    }

    protected function onlyDepartments(array $departments): self
    {
//        \Log::info('SIPs FROM RINGOSTAT@onlyDepartments START', [
//            'data' => $this->data,
//            'departments' => $departments,
//        ]);

        if(empty($departments)){
            return $this;
        }

        $tmp = [];

        foreach ($departments as $department) {
            foreach ($this->data as $k => $item) {

//
//                \Log::info('SIPs FROM RINGOSTAT@onlyDepartments Process', [
//                    '$department' => $department,
//                    '$item["departments"]' => $item['departments'],
//                    'equal' => in_array($department, $item['departments'])
//                ]);
                if(in_array($department, $item['departments'])){
                    $tmp[$k] = $item;
                }
            }
        }

        $this->data = $tmp;

//        \Log::info('SIPs FROM RINGOSTAT@onlyDepartments FINE', [
//            'data' => $this->data,
//            'tmp' => $tmp
//        ]);

        return $this;
    }

    protected function notOnCall(): self
    {
//        \Log::info('SIPs FROM RINGOSTAT@notOnCall START', [
//            $this->data
//        ]);

        foreach ($this->data as $k => $item) {
            if(
                Employee::query()
                    ->where('ringostat_id', $item['staffId'])
                    ->whereNotNull('ringostat_call_rec_id')
                    ->exists()
            ){
                unset($this->data[$k]);
            }
        }

//        \Log::info('SIPs FROM RINGOSTAT@notOnCall FINE', [
//            $this->data
//        ]);

        return $this;
    }

    protected function onlyOne(): self
    {
//        \Log::info('SIPs FROM RINGOSTAT@onlyOne START', [
//            $this->data
//        ]);

        $this->data = !empty($this->data) ? current($this->data) : [];

//        \Log::info('SIPs FROM RINGOSTAT@onlyOne FINE', [
//            $this->data
//        ]);

        return $this;
    }

    protected function getEmployeeEntity(): RingoEmployeeEntity
    {
        return RingoEmployeeEntity::fromRingoData($this->data);
    }

}


