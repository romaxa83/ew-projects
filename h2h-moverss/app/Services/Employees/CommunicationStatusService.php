<?php

namespace App\Services\Employees;

use App\Events\Communications\EmployeeStatus;
use App\Http\Controllers\Zadarma\PBXController;
use App\Models\Client\Phone;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Employee\PbxData;
use App\Models\Zadarma\CallsEvents;
use App\Services\Requests\Ringostat\Commands\GetSipStatusCommand;
use Illuminate\Support\Facades\Log;
use Zadarma_API\Response\PbxInternal;
use Zadarma_API\Webhook\AbstractNotify;

class CommunicationStatusService
{

    public function updateSipStatus(?Division $division = null): void
    {
        if ($division) {
            $this->zadarmaSipStatus($division);
            $this->ringostatSipStatus($division);
        } else {
            foreach (Division::all() as $division) {
                $this->zadarmaSipStatus($division);
                $this->ringostatSipStatus($division);
            }
        }
    }

    public function zadarmaSipStatus(?Division $division = null): void
    {
        if ($division) {
            $this->zadarmaFlow($division);
        } else {
            foreach (Division::all() as $division) {
                $this->zadarmaFlow($division);
            }
        }
    }

    private function zadarmaFlow(Division $division): void
    {
        try {
            $key = $division->miscs['zadarma_api_key'];
            $secret = $division->miscs['zadarma_api_secret'];

            /** @var $client \Zadarma_API\Api */
            $client = (new PBXController())->getAPI($key, $secret);

            /** @var $res PbxInternal */
            $res = $client->getPbxInternal();

            $numbers = $res->numbers ?? [];
            $pbxId = $res->pbx_id;

            $this->updateEmployeesPbxDataFromZadarma($client, $numbers, $pbxId);

            $this->updateEmployeesFromPbxData();

        } catch (\Throwable $e) {
            logger_ringostat('ZADARMA SIP STATUS FAIL' ,[$e->getMessage()]);
        }
    }

    public function ringostatSipStatus(?Division $division = null)
    {
        if ($division) {
            $this->ringoFlow($division);
        } else {
            foreach (Division::all() as $division) {
                $this->ringoFlow($division);
            }
        }
    }

    private function ringoFlow($division): void
    {
        try {
            $ringoData = $this->getRingostatSipStatusByDivision($division);

            $transformedData = $this->transformDataFromRingostat($ringoData);
            $this->updateEmployeesFromRingostat($transformedData);
        } catch (\Throwable $e) {
            logger_ringostat('RINGOSTAT SIP STATUS FAIL' ,[$e->getMessage()]);
        }
    }

    private function getRingostatSipStatusByDivision(Division $division): array
    {
//        logger_ringostat('[request] GetSipStatusCommand BY DIVISION: ' . $division->title);

        $command = resolve(GetSipStatusCommand::class, ['division' => $division]);
//        $command->setLogger(Log::channel('ringostat'));

        return $command->exec();
    }

    public function transformDataFromRingostat(array $data): array
    {
        $tmp = [];
        foreach ($data as $value) {
            $tmp[] = [
                'id' => $value['staffId'],
                'status' => $value['status'],
            ];
        }

        return $tmp;
    }

    public function updateEmployeesFromRingostat(array $data): void
    {
//        logger_ringostat('UPDATE EMPLOYEES DATA', $data);

        $ids = array_column($data, 'id');

        $users = Employee::query()
            ->whereIn('ringostat_id', $ids)
            ->get();


//        logger_ringostat('FIND EMPLOYEES  [' . $users->count() . ']');
        $updated = 0;

        foreach ($data as $item){
            $user = $users->where('ringostat_id', $item['id'])->first();
            if($user && $user->ringostat_sip_status != $item['status']){
                $user->update(['ringostat_sip_status' => $item['status']]);

                $updated++;

//                logger_ringostat('UPDATED EMPLOYEE  [' . $user->id . ']');
            }
        }

//        logger_ringostat('UPDATED EMPLOYEES  [' . $updated . ']');
    }

    public function updateEmployeesPbxDataFromZadarma(
        \Zadarma_API\Api $client,
        array $numbers,
        int $pbxId
    ): void
    {
        if(!empty($numbers)){
            Employee::query()
                ->with(['pbxdata'])
                ->whereHas('pbxdata', function ($query) use ($numbers, $pbxId) {
                    $query->whereIn('pbx_ext', $numbers)
                        ->where('pbx_id', $pbxId);
                })
                ->each(function (Employee $employee) use ($client, $pbxId) {

                    /** @var $pbx Employee\PbxData */
                    $pbx = $employee->pbxdata->where('pbx_id', $pbxId)->first();

                    $status = $client->getPbxStatus($pbx->pbx_ext)->is_online ?? false;
                    $status = to_bool($status);

                    if($pbx->sip_status != $status){
                        $pbx->update(['sip_status' => $status]);
                    }
                });
        }
    }

    public function updateEmployeesFromPbxData(): void
    {
        Employee::query()
            ->with(['pbxdata'])
            ->whereHas('pbxdata', function ($query) {
                $query->whereNotNull('pbx_ext');
            })
            ->each(function (Employee $employee) {
                self::updateEmployeeFromPbxData($employee);
            });
    }

    public static function updateEmployeeFromPbxData(Employee $employee)
    {
        $status = false;
        $recId = null;
        foreach ($employee->pbxdata as $pbx){
            /** @var $pbx Employee\PbxData */
            if($pbx->sip_status){
                $status = true;
            }
            if($pbx->call_rec_id){
                $recId = $pbx->call_rec_id;
            }
        }

        if($status != $employee->zadarma_sip_status){
            $employee->zadarma_sip_status = $status;
        }
        if($recId != $employee->zadarma_call_rec_id){

//            logger_zadarma('updateEmployeeFromPbxData', [
//                'recId' => $recId,
//                'employeeId' => $employee->toArray(),
//            ]);

            $employee->zadarma_call_rec_id = $recId;
        }

        if(!empty($employee->getDirty())){
            $employee->save();
        }
    }

    public static function updatePbxDataByCall(
        CallsEvents $callRecord,
        string $event
    )
    {
        if($event == AbstractNotify::EVENT_ANSWER){
            if(
                $pbxData = PbxData::query()
                    ->with('employee')
                    ->where('pbx_id', $callRecord->pbx_id)
                    ->where('pbx_ext', $callRecord->internal)
                    ->first()
            ){
                $pbxData->update(['call_rec_id' => $callRecord->id]);

                self::updateEmployeeFromPbxData($pbxData->employee);

                $value = Phone::clearPhone($callRecord->destination);
                if(
                    $phone = Phone::query()
                        ->where('value', "LIKE", $value)
                        ->first()
                ){
                    $callRecord->update(['client_id' => $phone->client_id]);
                }

                broadcast(new EmployeeStatus($pbxData->employee));
            }
        } elseif(
            $event == AbstractNotify::EVENT_END
            || $event == AbstractNotify::EVENT_OUT_END
        ) {
            if(
                $pbxData = PbxData::query()
                    ->with('employee')
                    ->whereNotNull('call_rec_id')
                    ->where('pbx_id', $callRecord->pbx_id)
                    ->where('pbx_ext', $callRecord->internal)
                    ->first()
            ){
                $pbxData->update(['call_rec_id' => null]);

                self::updateEmployeeFromPbxData($pbxData->employee);

                broadcast(new EmployeeStatus($pbxData->employee));
            }
        }
    }
}

