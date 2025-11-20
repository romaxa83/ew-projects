<?php

namespace App\Services\Employees;

use App\Dto\Employees\EmployeeDto;
use App\Enums\Employees\Status;
use App\Events\Employees\EmployeeUpdatedEvent;
use App\IPTelephony\Events\QueueMember\QueueMemberDeleteAndInsertEvent;
use App\IPTelephony\Events\Subscriber\SubscriberDeleteEvent;
use App\IPTelephony\Services\Storage\Kamailio\LocationService;
use App\Models\Employees\Employee;
use App\Notifications\Employees\SendCredentialsNotification;
use App\Repositories\Employees\EmployeeRepository;
use App\Repositories\Permissions\RoleRepository;
use App\Services\AbstractService;
use App\Services\Reports\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Ramsey\Uuid\Uuid;

class EmployeeService extends AbstractService
{
    public function __construct(
        protected RoleRepository $roleRepository,
        protected ReportService $reportService,
    )
    {
        $this->repo = resolve(EmployeeRepository::class);
        return parent::__construct();
    }

    public function create(EmployeeDto $dto): Employee
    {
        $model = new Employee();

        $model->guid = Uuid::uuid4();
        $model = $this->changeStatus($model, Status::FREE(), false);
        $model->setPassword($dto->password);
        $model->email_verified_at = CarbonImmutable::now();

        $this->fill($model, $dto);

        $model->save();

        $this->reportService->createEmpty($model->id);

        $role = $this->roleRepository->getEmployeeRole();
        $model->assignRole($role);

        $this->checkSipToLocation($model);

        return $model;
    }

    public function update(Employee $model, EmployeeDto $dto): Employee
    {
        if($dto->password){
            $model->setPassword($dto->password);
        }

        if(!$dto->sipID && $model->sip_id){
            event(new SubscriberDeleteEvent($model));
        }

        $this->fill($model, $dto);

        if($model->isDirty(
            ['first_name', 'sip_id', 'last_name']
        ) && $model->sip_id){
            event(new EmployeeUpdatedEvent($model));
        }

        if ($model->isDirty('department_id')){
            event(new QueueMemberDeleteAndInsertEvent($model));
        }

        $model->save();

        $this->checkSipToLocation($model);

        return $model;
    }

    public function changeStatus(Employee $model, Status $status, $save = true): Employee
    {
        $model->status = $status;

        if($save){
            $model->save();
        }

        logger_info("Employee [$model->id] set status [$status->value]");

        return $model;
    }

    protected function fill(Employee $model, EmployeeDto $dto): void
    {
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->email = $dto->email;
        $model->department_id = $dto->departmentID;
        $model->sip_id = $dto->sipID;
    }

    public function sendCredentialsNotification(Employee $model, string $password)
    {
        Notification::route('mail', $model->email->getValue())->notify(
            new SendCredentialsNotification($model, $password)
        );

        logger_info("SEND email for credentials to employee [{$model->email->getValue()}]");
    }

    // проверяет у пользователя наличие sip в таблице "location"(kamailio), и если нет проставляет статус "error"
    public function checkSipToLocation(Employee $model, array $locationsSip = [])
    {

        if(empty($locationsSip)){
            $service = resolve(LocationService::class);
            $locationsSip = $service->locationsUsername();
        }

        if(!($model->sip && array_key_exists($model->sip->number, $locationsSip))){
            $this->changeStatus($model, Status::ERROR());

            logger_info("[status] SET status error [{$model->id}]");
        }
    }

    public function remove(Employee $model)
    {
        return $this->delete($model->id);
    }
}
