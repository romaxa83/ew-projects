<?php

namespace App\Console\Commands\FixDB;

use App\Dto\Departments\DepartmentDto;
use App\Services\Departments\DepartmentService;
use Illuminate\Console\Command;

class AddDepartment extends Command
{
    protected $signature = 'fixdb:add_department';

    protected $description = '';

    public function __construct(
        protected DepartmentService $service,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $data = [
            'name' => 'Office Manager',
            'active' => false,
        ];

        try {
            if(!$this->service->repo->existBy(['name' => $data['name']])){
                $this->service->create(DepartmentDto::byArgs($data));
            }
        } catch (\Exception $e){
            $this->error($e->getMessage());
        }
    }
}
