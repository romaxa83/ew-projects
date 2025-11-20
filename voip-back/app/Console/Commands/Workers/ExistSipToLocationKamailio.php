<?php

namespace App\Console\Commands\Workers;

use App\Enums\Employees\Status;
use App\IPTelephony\Services\Storage\Kamailio\LocationService;
use App\Models\Employees\Employee;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\Employees\EmployeeService;
use Illuminate\Console\Command;

class ExistSipToLocationKamailio extends Command
{
    protected $signature = 'workers:exist_sip_to_location';

    protected $description = 'Проверяет есть ли запись sip в таблице "location"(kamailio), для тех записей , которых нет проставляется статус "error"';

    public function __construct(
        protected EmployeeRepository $employeeRepository,
        protected EmployeeService $employeeService,
        protected LocationService $locationService,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $locationsSip = $this->locationService->locationsUsername();

        $models = $this->employeeRepository->getAll(['sip']);

        foreach ($models as $model){
            /** @var $model Employee */
            $this->employeeService->checkSipToLocation($model, $locationsSip);
        }
    }
}


