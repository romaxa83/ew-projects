<?php

namespace App\Http\Controllers\Company;

use App\DataTables\Company\EmployeeRecordsDataTable;
use App\Enums\Employee\SalesTeamEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Zadarma\PBXController;
use App\Http\Requests\Company\EmployeeRequest;
use App\Jobs\Employees\RingostatSyncJob;
use App\Mail\Messages;
use App\Services\CashRegistry\CashRegistryService;
use App\Models\{CashRegistry\CashRegistry,
    Client\ClientToTag,
    Client\MessengerType,
    Division,
    Employee,
    Partners\Partner,
    User\Role};
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use DB, Auth, Exception, Hash, Mail, Str, Validator;


/**
 * Manage Employee records
 */
class EmployeeController extends Controller
{
    /**
     * Append message to response.
     * @var string
     */
    private string $append_msg = '';

    /**
     * test @see \Tests\Feature\Company\Employee\AjaxInfoTest
     * Get record info for AJAX.
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxInfo(Request $request): JsonResponse
    {
        $record_id = (int)$request->route('id');
        $record = Employee::record()->findOrFail($record_id);

        return response()
            ->json([
                'success' => true,
                'record' => $record,
                'types' => [
                    'partners' => Partner::get(['id', 'name'])->keyBy('id'),
                    'phones' => config('app.phone_types'),
                    'messengers' => MessengerType::get(['id', 'title', 'icon'])->keyBy('id'),
                    'roles' => Role::orderBy('title')->get(['id', 'title'])->keyBy('id'),
                    'divisions' => Division::get(['id', 'title'])->keyBy('id'),
                    'pbx' => Division::get(['id', 'title', 'miscs->zadarma_pbx_id as pbx_id']),
                    'sales_team' => SalesTeamEnum::forSelect('key'),
                ],
            ]);
    }


    public function employeesAutocompleteAjax(Request $request)
    {
        $Employees = User::with(['roles:id,title'])
            ->where('active', 1)
            ->whereHas('employee', function (Builder $query) {
                $query->whereJsonContains('division_ids', [(int)session()->get('division.id')]);
            })
            ->when($request->q, function ($q, $term) {
                $q->where('name', 'LIKE', $term . '%');
            })
            ->get(['id', 'active', 'name']);

        return response()
            ->json([
                'success' => true,
                'results' => $Employees->toArray(),
                'pagination' => [
                    'more' => false
                ]
            ]);
    }


    /**
     * Create new Employee with base rows (name, last name, email).
     * @param Request $request
     * @param Employee $record
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createEmpty(Request $request, Employee $record): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'l_name' => 'nullable|string|max:70',
            'email' => 'required|string|email|unique:employees_emails,value|max:60',
            'phone' => 'nullable|string|max:60',
        ], [
            'email.unique' => 'The :attribute has already been taken (searching in all divisions).',
        ]);

        if ($validator->fails()) {
            return redirect()->route('company.employees.records')
                ->withErrors($validator)
                ->withInput();
        }


        $validated = $validator->validated();
        $validated['active'] = 1;
        $validated['division_ids'][] = request()->session()->get('division.id');
        /**
         * @var $NewEmployee Employee
         */
        $NewEmployee = $record->create($validated);

        $NewEmployee->emails()
            ->create([
                'is_primary' => 1,
                'value' => $validated['email'],
            ]);
        if (!empty($validated['phone'])) {
            $NewEmployee->phones()
                ->create([
                    'is_primary' => 1,
                    'type_id' => 1,
                    'value' => $validated['phone'],
                ]);
        }

        return redirect()->route('company.employees.record', ['id' => $NewEmployee->id]);
    }

    /**
     * Save Employee record.
     * @param EmployeeRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function save(EmployeeRequest $request): JsonResponse
    {
        $serviceCashRegistry = resolve(CashRegistryService::class);

        $validated = $request->validated();

        $record = Employee::record()->findOrFail($validated['id']);
        $oldFullName = $record->full_name;

        $partnerRole = Role::query()->select(['id'])->partner()->first();

        try {
            if(
                $partnerRole
                && isset($validated['roles'])
                && !empty($validated['roles'])
                && in_array($partnerRole->id, $validated['roles'])
            ) {
                // если добавляют роль "партнера", все остальные роли удаляются
                $validated['roles'] = [$partnerRole->id];
            }

            if(
                isset($validated['roles'])
                && !empty($validated['roles'])
                && in_array(Role::FOREMAN_ID, $validated['roles'])
            ){
                if(!$record->user->isForeman()){
                    if(
                        $cashRegistry = CashRegistry::query()
                            ->where('employee_id', $record->id)
                            ->first()
                    ){
                        $cashRegistry->update(['active' => true]);
                    } else {
                        $serviceCashRegistry->create($record);
                    }
                }
            } elseif (
                isset($validated['roles'])
                && !empty($validated['roles'])
                && !in_array(Role::FOREMAN_ID, $validated['roles'])
            ){

                if($record->user && $record->user->isForeman()){
                     CashRegistry::query()
                        ->where('employee_id', $record->id)
                        ->update(['active' => false]);
                }
            }

            $record->fill($validated);
            $record->active = $validated['active'];

            $changed = $record->isDirty() ? 1 : 0;
            DB::transaction(function () use ($record, $validated, &$changed, $oldFullName) {
                $changed += $record->updateRelations('pbxdata', $validated['pbx_data'] ?? []);
                $changed += $record->updateRelations('phones', $validated['phones'] ?? []);
                $changed += $record->updateRelations('emails', $validated['emails'] ?? []);
                $changed += $record->updateRelations('messengers', $validated['messengers'] ?? []);
                $changed += $record->updateRelations('notes', $validated['notes'] ?? []);
                $changed += $record->updateRelations('busyDates', $validated['busy_dates'] ?? []);
                $changed += $this->saveUser($record, $validated);

                if (!$record->busyWeeksDays || (count($validated['busy_weeks_days']['miscs']) !== count($record->busyWeeksDays->miscs))) {
                    $record->busyWeeksDays()
                        ->updateOrCreate(
                            [
                                'employee_id' => $record->id,
                            ],
                            [
                                'user_id' => Auth::user()->id,
                                'miscs' => $validated['busy_weeks_days']['miscs'],
                            ]);
                    $changed++;
                }

                if ($record->isDirty()) {
                    $record->save();

                    if($record->rates->isNotEmpty()){
                        $record->rates()->update([
                            'employee_name' => $record->full_name,
                        ]);
                    }

                    if($oldFullName !== $record->full_name){
                        ClientToTag::query()
                            ->where('employee_id', $record->id)
                            ->update(['employee_name' => $record->full_name]);
                    }
                }
            });
            $PBXController = new PBXController();
            Cache::put('is_zadarma_enabled_' . $record->auth_user_id,
                $PBXController->hasZadarma() && $PBXController->getEmployeePbxExtention($record->auth_user_id), now()->addHours(6));
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }

        $record = Employee::record()->find($validated['id']);

        dispatch(new RingostatSyncJob());

        return response()
            ->json([
                'success' => true,
                'msg' => ($changed ? 'Employee changed.' : 'Changed nothing.') . $this->append_msg,
                'record' => $record,
                'cache' => Cache::get('is_zadarma_enabled_' . $record->auth_user_id)
            ]);
    }

    /**
     * Get records for DT.
     * @param EmployeeRecordsDataTable $dataTable
     * @return mixed
     */
    public function records(EmployeeRecordsDataTable $dataTable)
    {
        return $dataTable->render('layouts.company.employee.records', [
            'divisions' => Division::get(['id', 'title']),
            'roles' => Role::orderBy('id')->get(['id', 'title'])
        ]);
    }

    /**
     * Save User record.
     * TODO Should bee in User controller
     * @param $record
     * @param $validated
     * @return int
     */
    private function saveUser($record, $validated): int
    {
        $email = $record->emails()
            ->orderBy('is_primary', 'desc')
            ->orderBy('sort', 'asc')
            ->first();

        if ($record->user) {
            $user = $record->user()->first();
        } else {
            $new_password = substr(time(), 0, 7);

            $user = new User();
            $user->name = $record->name;
            $user->email = rand(0, 999) . '@fakeEmail.com';
            $user->password = Hash::make($new_password);
            $user->save();
            $user->load('roles');

            $record->auth_user_id = $user->id;
            $record->save();
        }

        // Changing email
        if ($email && $user->email !== $email->value) {
            $exists = User::where([
                ['email', '=', $email->value],
                ['id', '!=', $record->auth_user_id],
            ])->exists();
            if (!$exists) {
                $user->email = $email->value;
            } else {
                $this->append_msg = ' This email address already exists in another Employer.';
            }
        }

        $user->roles()->sync($validated['roles'] ?? []);

        // Account status
        $active = $validated['user']['active'];
        if (!$validated['active']) {
            $active = 0;
        }
        $user->active = $active;
        $user->division_ids = $validated['division_ids'];

        if ($validated['send_welcome'] && $user->email) {
            $password = Str::random(8);

            $user->password = Hash::make($password);

            Mail::to($user->email)->queue(new Messages('emails.sys_message', [
                'subject' => 'Welcome to ' . config('app.name'),
                'msg' => "Hello, {$user->name}!\n\n" .
                    "Here is you login information:\n" .
                    config('app.url') . PHP_EOL .
                    "Login: {$user->email}\n" .
                    "Password: {$password}\n"
            ]));
            $this->append_msg = ' Welcome email sent.';
        }


        if ($user->isDirty()) {
            $user->save();
        }

        return 1;
    }

}
