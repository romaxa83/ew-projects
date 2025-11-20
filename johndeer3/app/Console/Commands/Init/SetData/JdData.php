<?php

namespace App\Console\Commands\Init\SetData;

use App\Console\Commands\Init\Helpers\WriteDataToCsv;
use App\DTO\JD\DealerDTO;
use App\DTO\JD\EquipmentGroupDTO;
use App\DTO\JD\ManufactureDTO;
use App\DTO\JD\ModelDescriptionDTO;
use App\DTO\JD\ProductDTO;
use App\DTO\JD\RegionDTO;
use App\DTO\JD\SizeParameterDTO;
use App\Models\User\Nationality;
use App\Repositories\JD\ClientRepository;
use App\Repositories\JD\CountryRepository;
use App\Repositories\JD\DealersRepository;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\JD\ProductRepository;
use App\Repositories\JD\SizeParameterRepository;
use App\Repositories\JD\ManufacturerRepository;
use App\Repositories\JD\ModelDescriptionRepository;
use App\Repositories\NationalityRepository;
use App\Repositories\JD\RegionsRepository;
use App\Services\Catalog\CountryService;
use App\Services\JD\ClientService;
use App\Services\JD\DealerService;
use App\Services\JD\EquipmentGroupService;
use App\Services\JD\ProductService;
use App\Services\JD\SizeParameterService;
use App\Services\JD\ManufacturerService;
use App\Services\JD\ModelDescriptionService;
use App\Services\JD\RegionService;
use Carbon\Carbon;

class JdData
{
    private $egId;

    private $equipmentGroupRepository;
    private $equipmentGroupService;
    private $modelDescriptionRepository;
    private $modelDescriptionService;
    private $sizeParameterRepository;
    private $sizeParameterService;
    private $productRepository;
    private $productService;
    private $nationalityRepository;
    private $dealersRepository;
    private $dealerService;
    private $manufacturerService;
    private $manufacturerRepository;
    private $regionsRepository;
    private $regionsService;
    private $clientRepository;
    private $clientService;
    private $countryRepository;
    private $countryService;

    public function __construct(
        EquipmentGroupRepository $equipmentGroupRepository,
        EquipmentGroupService $equipmentGroupService,
        ModelDescriptionRepository $modelDescriptionRepository,
        ModelDescriptionService $modelDescriptionService,
        SizeParameterRepository $sizeParameterRepository,
        SizeParameterService $sizeParameterService,
        ProductRepository $productRepository,
        ProductService $productService,
        NationalityRepository $nationalityRepository,
        DealersRepository $dealersRepository,
        DealerService $dealerService,
        ManufacturerService $manufacturerService,
        ManufacturerRepository $manufacturerRepository,
        RegionsRepository $regionsRepository,
        RegionService $regionsService,
        ClientRepository $clientRepository,
        ClientService $clientService,
        CountryRepository $countryRepository,
        CountryService $countryService
    )
    {
        $this->equipmentGroupRepository = $equipmentGroupRepository;
        $this->equipmentGroupService = $equipmentGroupService;
        $this->modelDescriptionRepository = $modelDescriptionRepository;
        $this->modelDescriptionService = $modelDescriptionService;
        $this->sizeParameterRepository = $sizeParameterRepository;
        $this->sizeParameterService = $sizeParameterService;
        $this->productRepository = $productRepository;
        $this->productService = $productService;
        $this->nationalityRepository = $nationalityRepository;
        $this->dealersRepository = $dealersRepository;
        $this->dealerService = $dealerService;
        $this->manufacturerService = $manufacturerService;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->regionsRepository = $regionsRepository;
        $this->regionsService = $regionsService;
        $this->clientRepository = $clientRepository;
        $this->clientService = $clientService;
        $this->countryRepository = $countryRepository;
        $this->countryService = $countryService;
    }

    public function run(): void
    {
        $this->setEquipmentGroupAndModelDescription();
        $this->setSizeParameters();
        $this->setProducts();
        $this->setNationalities();
        $this->setDealers();
        $this->setManufactures();
        $this->setRegions();
        $this->setClients();
        $this->setCountries();
    }

    private function setCountries(): void
    {
        $path = $this->getPath(WriteDataToCsv::COUNTRIES);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!$this->countryRepository->existBy('name', $item[0])){
                            $this->countryService->create([
                                "name" => $item[0],
                                "active" => $item[1] == 'true' ? true: false,
                            ]);
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setRegions(): void
    {
        $path = $this->getPath(WriteDataToCsv::REGIONS);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!$this->regionsRepository->existBy('jd_id', $item[0])){
                            $this->regionsService->createFromImport(
                                RegionDTO::byArgs([
                                    "id" => $item[0],
                                    "name" => $item[1],
                                    "status" => $item[2] == 'true' ? 1: 0,
                                ])
                            );
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setClients(): void
    {
        $path = $this->getPath(WriteDataToCsv::CLIENTS);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    $now = Carbon::now();
                    if($index != 0){
                        if(!$this->clientRepository->existBy('jd_id', $item[0])){
                            $this->clientService->createFromImport([
                                "id" => $item[0],
                                "customer_id" => $item[1] ?: null,
                                "company_name" => $item[2] ?: null,
                                "customer_first_name" => $item[3] ?: null,
                                "customer_last_name" => $item[4] ?: null,
                                "customer_second_name" => $item[5] ?: null,
                                "phone" => $item[6] ?: null,
                                "status" => $item[7] == 'true' ? true: false,
                                "created_at" => $now,
                                "region_id" => $item[8] ?: null,
                            ]);
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setProducts(): void
    {
        $path = $this->getPath(WriteDataToCsv::PRODUCTS);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!$this->productRepository->existBy('jd_id', $item[0])){
                            $this->productService->createFromImport(ProductDTO::byArgs([
                                "id" => $item[0],
                                "size_name" => $item[1] != '' ? $item[1] : null,
                                "type" => $item[2] != '' ? $item[2] : null,
                                "model_description_id" => $item[3] != '' ? $item[3] : null,
                                "equipment_group_id" => $item[4] != '' ? $item[4] : null,
                                "manufacture_id" => $item[5] != '' ? $item[5] : null,
                                "size_parameter_id" => $item[6] != '' ? $item[6] : null,
                                "status" => $item[7] == 'true' ? true: false
                            ]));
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setManufactures(): void
    {
        $path = $this->getPath(WriteDataToCsv::MANUFACTURERS);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!$this->manufacturerRepository->existBy('jd_id', $item[0])){
                            $this->manufacturerService->createFromImport(
                                ManufactureDTO::byArgs([
                                    "id" => $item[0],
                                    "name" => $item[1],
                                    "status" => $item[2] == 'true' ? 1: 0,
                                    "relationship" => $item[3],
                                    "position" => $item[4],
                                ])
                            );
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setSizeParameters(): void
    {
        $path = $this->getPath(WriteDataToCsv::SIZE_PARAMETERS);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!$this->sizeParameterRepository->existBy('jd_id', $item[0])){
                            $this->sizeParameterService->createFromImport(SizeParameterDTO::byArgs([
                                "id" => $item[0],
                                "name" => $item[1],
                                "status" => $item[2] == 'true' ? true: false
                            ]));
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setNationalities(): void
    {
        $path = $this->getPath(WriteDataToCsv::NATIONALITIES);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!$this->nationalityRepository->existBy('alias', $item[0])){
                            $model = new Nationality();
                            $model->alias = $item[0];
                            $model->name = $item[1];
                            $model->active = $item[2] == 'true' ? true: false;
                            $model->save();
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setDealers(): void
    {
        $path = $this->getPath(WriteDataToCsv::DEALERS);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!$this->dealersRepository->existBy('jd_id', $item[0])){
                            $this->dealerService->createFromImport(
                                DealerDTO::byArgs([
                                    "id" => $item[0],
                                    "jd_id" => $item[1],
                                    "name" => $item[2],
                                    "country" => $item[3],
                                    "status" => $item[4] == 'true' ? 1 : 0,
                                ])
                            );
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    private function setEquipmentGroupAndModelDescription(): void
    {
        $path = $this->getPath(WriteDataToCsv::EG_MD);
        $data = array_map('str_getcsv', file($path));
        try {
            \DB::transaction(function () use ($data, $path) {
                collect($data)->each(function($item, $index){
                    if($index != 0){
                        if(!empty($item[0])){
                            $this->egId = $item[0];
                            if(!$this->equipmentGroupRepository->existBy('jd_id', $item[0])){
                                $this->equipmentGroupService->createFromImport(
                                    EquipmentGroupDTO::byArgs([
                                        'id' => $item[0],
                                        'name' => $item[1],
                                        'status' => $item[2] == 'true' ? 1 : 0,
                                        'for_statistic' => $item[3] == 'true' ? 1 : 0,
                                    ])
                                );
                            }
                        } else {
                            if(!$this->modelDescriptionRepository->existBy('jd_id', $item[0])){
                                $this->modelDescriptionService->createFromImport(
                                    ModelDescriptionDTO::byArgs([
                                        'id' => $item[4],
                                        'name' => $item[5],
                                        'equipment_group_id' => $this->egId,
                                        'status' => $item[6] == 'true' ? 1 : 0,
                                    ])
                                );
                            }
                        }
                    }
                });
                echo "Set data from [{$path}] to db" . PHP_EOL;
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function getPath(string $name): string
    {
        $f = explode('/',__FILE__);
        $f[count($f) - 2] = 'Helpers';
        $f[count($f) - 1] =  $name.'.csv';

        return implode('/', $f);
    }
}
