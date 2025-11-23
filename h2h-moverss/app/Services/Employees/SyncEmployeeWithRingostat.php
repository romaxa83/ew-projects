<?php

namespace App\Services\Employees;

use App\Enums\Common\LogKeyEnum;
use App\Models\Division;
use App\Models\Employee;
use App\Services\Requests\Ringostat\Commands\GetSipStatusCommand;
use App\User;
use Illuminate\Support\Facades\Log;

class SyncEmployeeWithRingostat
{
    private $logData = [];

    public function exec(?Division $division = null)
    {
        if ($division) {
            $this->ringoFlow($division);
        } else {
            foreach (Division::all() as $division) {
                $this->ringoFlow($division);
            }
        }

        Log::info(LogKeyEnum::SyncRingostat() . 'SyncEmployeeWithRingostat', $this->logData);
    }

    private function ringoFlow(Division $division): void
    {
        try {
            $ringoData = $this->getRingostatSipStatusByDivision($division);
            $this->logData[$division->short]['ringoData'] = $ringoData;

            $transformedData = $this->transformDataFromRingostat($ringoData);
            $this->logData[$division->short]['transformedData'] = $transformedData;

            $this->updateEmployeesFromRingostat($transformedData, $division);

        } catch (\Throwable $e) {
            Log::error(LogKeyEnum::SyncRingostat() . 'FAIL in service SyncEmployeeWithRingostat', [
                'error' => $e,
                'logData' => $this->logData,
            ]);
        }
    }

    private function getRingostatSipStatusByDivision(Division $division): array
    {
        $command = resolve(GetSipStatusCommand::class, ['division' => $division]);

        return $command->exec();
    }

    public function transformDataFromRingostat(array $data): array
    {
        $tmp = [];
        foreach ($data as $k => $value) {
            $tmp[$k] = [
                'email' => $value['email'],
                'id' => $value['staffId'],
                'miscs' => [
                    'ext_number' => $value['extensionNumber'],
                    'sip_directions' => ''
                ],
            ];

            foreach ($value['directions']['additional'] ?? [] as $item) {
                if(isset($item['type']) && $item['type'] == 'sip'){
                    $tmp[$k]['miscs']['sip_direction'] = $item['direction'] ?? null;
                    $tmp[$k]['miscs']['sip_directions'] .= isset($item['direction']) ? $item['direction'] . ',': null;
                }
            }

            foreach ($value['directions']['main'] ?? [] as $item) {
                if(isset($item['type']) && $item['type'] == 'sip'){
                    $tmp[$k]['miscs']['sip_direction'] = $item['direction'] ?? null;
                    $tmp[$k]['miscs']['sip_directions'] .= isset($item['direction']) ? $item['direction'] . ',' : null;
                }
            }
        }

        return array_values($tmp);
    }

    public function updateEmployeesFromRingostat(array $data, Division $division): void
    {
        $emails = array_column($data, 'email');

        $users = User::query()
            ->with('employee')
            ->where('active', 1)
            ->whereIn('email', $emails)
            ->get()
            ->map(function (User $user) {
                $user->email = strtolower($user->email);
                return $user;
            })
        ;

        $usersIDs = $users->pluck('id')->toArray();

        foreach ($data as $item){
            $user = $users->where('email', strtolower($item['email']))->first();
            if(
                $user
                && $user->employee
                && $user->employee->isActive()
            ) {
                if(isset($item['miscs']['sip_directions'])) {
                    $item['miscs']['sip_directions'] = rtrim($item['miscs']['sip_directions'], ',');
                }

                $this->logData[$division->short]['userUpdate'][] = [
                    'user_id' => $user->id,
                    'user_email' => $item['email'],
                    'ringostat_id' => $item['id'],
                    'ringostat_miscs' => $item['miscs'],
                ];

                $user->employee()
                    ->where('active', 1)
                    ->update([
                        'ringostat_id' => $item['id'],
                        'ringostat_miscs' => $item['miscs']
                    ]);
            }
        }

        // delete old
        Employee::query()
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('active', 1);
            })
            ->where('active', 1)
            ->whereNotIn('auth_user_id', $usersIDs)
            ->whereNotNull('ringostat_id')
            ->where('division_ids', '['.$division->id.']')
            ->each(function (Employee $employee) use ($division) {
                $this->logData[$division->short]['userRemoveRingoData'][] = [
                    'user_id' => $employee->auth_user_id,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                ];
                $employee->update([
                    'ringostat_id' => null,
                    'ringostat_miscs' => null
                ]);
            });

    }
}


