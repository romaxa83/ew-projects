<?php

namespace App\Console\Commands\Worker;

use App\DTO\JD\DealerDTO;
use App\DTO\JD\EquipmentGroupDTO;
use App\DTO\JD\ManufactureDTO;
use App\DTO\JD\ModelDescriptionDTO;
use App\DTO\JD\ProductDTO;
use App\DTO\JD\RegionDTO;
use App\DTO\JD\SizeParameterDTO;
use App\DTO\JD\UserDTO;
use App\Models\JD\Client;
use App\Models\User\Role;
use App\Models\User\User;
use App\Models\Version;
use App\Repositories\JD\ClientRepository;
use App\Repositories\JD\DealersRepository;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\JD\ProductRepository;
use App\Repositories\JD\SizeParameterRepository;
use App\Repositories\JD\ManufacturerRepository;
use App\Repositories\JD\ModelDescriptionRepository;
use App\Repositories\JD\RegionsRepository;
use App\Repositories\User\RoleRepository;
use App\Repositories\User\UserRepository;
use App\Services\JD\DealerService;
use App\Services\JD\EquipmentGroupService;
use App\Services\Import\ImportService;
use App\Services\JD\ProductService;
use App\Services\JD\SizeParameterService;
use App\Services\JD\ManufacturerService;
use App\Services\JD\ModelDescriptionService;
use App\Services\JD\RegionService;
use App\Services\Telegram\TelegramDev;
use App\Services\UserService;
use Illuminate\Console\Command;

class SyncJD extends Command
{
    protected $signature = 'jd:sync';

    protected $description = 'Синхронизация данных с johnDeer1';

    protected $msg = " 🔄 \n";

    public function __construct(protected ImportService $importService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->syncRegions();
        $this->syncEquipmentGroup();
        $this->syncModelDescription();
        $this->syncDealers();
        $this->syncClients();
        $this->syncTM();
        $this->syncSM();
        $this->syncManufacturer();
        $this->syncSizeParameters();
        $this->syncProducts();

        TelegramDev::info($this->msg);
        \Log::notice("Sync JD DATA \n {$this->msg}");
    }

    public function syncModelDescription(): void
    {
        try {
            $data = $this->importService->getData(ImportService::MD);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_MD)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(ModelDescriptionService::class);
                $repo = app(ModelDescriptionRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = ModelDescriptionDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $service->createFromImport($dto);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] MD - updated [{$updateCount}], created [{$newCount}] \n";
                Version::setVersion(Version::IMPORT_MD, $newVersion);
                Version::setVersion(Version::MODEL_DESCRIPTION, Version::getHash($repo->getForHash()));
            } else {
                $this->msg .=  "[❌] MD - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] MD - {$e->getMessage()} \n";
        }
    }

    public function syncRegions(): void
    {
        try {
            $data = $this->importService->getData(ImportService::REGION);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_REGION)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(RegionService::class);
                $repo = app(RegionsRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = RegionDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $service->createFromImport($dto);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] REGION - updated [{$updateCount}], created [{$newCount}] \n";
                Version::setVersion(Version::IMPORT_REGION, $newVersion);
            } else {
                $this->msg .=  "[❌] REGION - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] REGION - {$e->getMessage()} \n";
        }
    }

    public function syncEquipmentGroup(): void
    {
        try {
            $data = $this->importService->getData(ImportService::EG);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_EG)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(EquipmentGroupService::class);
                $repo = app(EquipmentGroupRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = EquipmentGroupDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $service->createFromImport($dto);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] EG - updated [{$updateCount}], created [{$newCount}] \n";
                Version::setVersion(Version::IMPORT_EG, $newVersion);
                Version::setVersion(Version::EQUIPMENT_GROUP, Version::getHash($repo->getAllForHash()));
            } else {
                $this->msg .=  "[❌] EG - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] EG - {$e->getMessage()} \n";
        }
    }

    public function syncDealers(): void
    {
        try {
            $data = $this->importService->getData(ImportService::DEALER);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_DEALER)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(DealerService::class);
                $repo = app(DealersRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = DealerDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        // меняем статус всем привязаным ps данного дилера
                        if($model->users_ps){
                            $model->users_ps->each(function (User $item) use($dto) {
                                $item->update(["status" => $dto->status]);
                            });
                        }
                        $updateCount++;
                    } else {
                        $service->createFromImport($dto);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] DEALER - updated [{$updateCount}], created [{$newCount}] \n";
                Version::setVersion(Version::IMPORT_DEALER, $newVersion);
                Version::setVersion(Version::DEALERS, Version::getHash($repo->getForHash()));
            } else {
                $this->msg .=  "[❌] DEALER - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] DEALER - {$e->getMessage()} \n";
        }
    }

    public function syncClients(): void
    {
        try {
            $clients = $this->importService->getData(ImportService::CLIENT);
            $newVersion = Version::getHash($clients);

            if(!Version::checkVersion($newVersion,Version::IMPORT_CLIENT)){
                $updateCount = 0;

                foreach(array_chunk($clients ?? [], 500) as $chunk){
                    $data = [];
                    foreach ($chunk as $key => $client){

                        $data[$key]['jd_id'] = $client['id'];
                        $data[$key]['customer_id'] = $client['customer_id'];
                        $data[$key]['company_name'] = $client['company_name'];
                        $data[$key]['customer_first_name'] = $client['customer_first_name'];
                        $data[$key]['customer_last_name'] = $client['customer_last_name'];
                        $data[$key]['customer_second_name'] = $client['customer_second_name'];
                        $data[$key]['phone'] = $client['phone'];
                        $data[$key]['status'] = $client['status'];
                        $data[$key]['created_at'] = $client['created_at'];
                        $data[$key]['updated_at'] = $client['updated_at'];
                        $data[$key]['region_id'] = $client['region_id'];
                    }
                    array_values($data);

                    $columns = [
                        'customer_id', 'company_name', 'customer_first_name', 'customer_last_name', 'customer_second_name',
                        'phone', 'status', 'created_at', 'updated_at', 'region_id'
                    ];
                    $result = Client::insertOnDuplicateKey($data, $columns);

                    $updateCount += (int)$result;
                }

                $repo = app(ClientRepository::class);

                $this->msg .= "[✔] CLIENT - updated/created [{$updateCount}] \n";
                Version::setVersion(Version::IMPORT_CLIENT, $newVersion);
                Version::setVersion(Version::CLIENTS, Version::getHash($repo->getForHash()));
            } else {
                $this->msg .=  "[❌] CLIENT - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] CLIENT - {$e->getMessage()} \n";
        }
    }

    public function syncTM(): void
    {
        try {
            $data = $this->importService->getData(ImportService::TM);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_TM)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(UserService::class);
                $repo = app(UserRepository::class);
                $repoRole = app(RoleRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = UserDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $user = $service->createFromImport($dto, User::generateRandomPassword());
                        $role = $repoRole->findBy("role", Role::ROLE_TM);
                        $user->roles()->attach($role);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] TM - updated/created [{$updateCount}] \n";
                Version::setVersion(Version::IMPORT_TM, $newVersion);
            } else {
                $this->msg .=  "[❌] TM - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] TM - {$e->getMessage()} \n";
        }
    }

    public function syncSM(): void
    {
        try {
            $data = $this->importService->getData(ImportService::SM);

            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_SM)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(UserService::class);
                $repo = app(UserRepository::class);
                $repoRole = app(RoleRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = UserDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $user = $service->createFromImport($dto, User::generateRandomPassword());
                        $role = $repoRole->findBy('role',Role::ROLE_SM);
                        $user->roles()->attach($role);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] SM - updated/created [{$updateCount}] \n";
                Version::setVersion(Version::IMPORT_SM, $newVersion);
            } else {
                $this->msg .=  "[❌] SM - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] SM - {$e->getMessage()} \n";
        }
    }

    public function syncManufacturer(): void
    {
        try {
            $data = $this->importService->getData(ImportService::MANUFACTURE);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_MANUFACTURE)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(ManufacturerService::class);
                $repo = app(ManufacturerRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = ManufactureDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $service->createFromImport($dto);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] MANUFACTURE - updated [{$updateCount}], created [{$newCount}] \n";
                Version::setVersion(Version::IMPORT_MANUFACTURE, $newVersion);
                Version::setVersion(Version::MANUFACTURER, Version::getHash($repo->getForHash()));
            } else {
                $this->msg .=  "[❌] MANUFACTURE - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] MANUFACTURE - {$e->getMessage()} \n";
        }
    }

    public function syncSizeParameters(): void
    {
        try {
            $data = $this->importService->getData(ImportService::SIZE_PARAMETERS);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_SP)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(SizeParameterService::class);
                $repo = app(SizeParameterRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = SizeParameterDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $service->createFromImport($dto);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] SIZE PARAMETERS - updated [{$updateCount}], created [{$newCount}] \n";
                Version::setVersion(Version::IMPORT_SP, $newVersion);
            } else {
                $this->msg .=  "[❌] SIZE PARAMETERS - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] SIZE PARAMETERS - {$e->getMessage()} \n";
        }
    }

    public function syncProducts(): void
    {
        try {
            $data = $this->importService->getData(ImportService::PRODUCT);
            $newVersion = Version::getHash($data);

            if(!Version::checkVersion($newVersion,Version::IMPORT_PRODUCT)){
                $newCount = 0;
                $updateCount = 0;

                $service = app(ProductService::class);
                $repo = app(ProductRepository::class);

                foreach ($data ?? [] as $item){
                    $dto = ProductDTO::byArgs($item);
                    if($model = $repo->getBy('jd_id', $dto->jdID)){
                        $service->updateFromImport($model, $dto);
                        $updateCount++;
                    } else {
                        $service->createFromImport($dto);
                        $newCount++;
                    }
                }

                $this->msg .= "[✔] PRODUCT - updated [{$updateCount}], created [{$newCount}] \n";
                Version::setVersion(Version::IMPORT_PRODUCT, $newVersion);
            } else {
                $this->msg .=  "[❌] PRODUCT - not change \n";
            }
        } catch (\Exception $e) {
            $this->msg .=  "[⚠] PRODUCT - {$e->getMessage()} \n";
        }
    }
}
