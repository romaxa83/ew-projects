<?php

namespace Database\Seeders;

use App\Models\JD\Client;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\User\Profile;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\User\RoleRepository;
use App\Services\Report\ReportService;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class TestDataSeeder extends Seeder
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;
    /**
     * @var ReportService
     */
    private $reportService;

    public function __construct(
        RoleRepository $roleRepository,
        ReportService $reportService
    )
    {
        $this->roleRepository = $roleRepository;
        $this->reportService = $reportService;
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        $this->fillUser();
        $this->fillReport();
    }

    /**
     * @throws Exception
     */
    public function fillUser()
    {
        $roles = [Role::ROLE_PS, Role::ROLE_PSS];

        for($i=0; $i < 20; $i++){

            $role = $this->roleRepository->getRole($roles[rand(0,1)]);

            $user = factory(User::class)->create([
                'dealer_id' => $this->getDealer()->id
            ]);

            $user->profile()->save(factory(Profile::class)->make());
            $user->roles()->attach($role);
        }

    }

    /**
     * @throws Exception
     */
    public function fillReport()
    {
        $faker = resolve(Faker::class);

        for($i=0; $i < 20; $i++){
            $data = (object)[
                'salesman_name' => $faker->firstName,
                'clients' => [
                    [
                        'client_id' => $this->getClient()->id,
                        'type' => 1,
                        'name_machine' => $faker->firstName,
                        'comment' => $faker->sentence(5)
                    ],
                    [
                        'client_id' => $this->getClient()->id,
                        'type' => 0,
                        'name_machine' => $faker->firstName,
                    ]

                ],
                'machines' => [
                    [
                        'equipment_group_id' => $this->getEquipmentGroup()->id,
                        'model_description_id' => $this->getModelDescription()->id,
                        'header_brand' => "header_brand_{$faker->title}",
                        'header_model' => "header_model_{$faker->title}",
                        'serial_number_header' => "serial_number_header_{$faker->title}",
                        'machine_serial_number' => "machine_serial_number_{$faker->title}",
                        'trailed_equipment_type' => "trailed_equipment_type_{$faker->title}",
                    ],
                    [
                        "equipment_group" => "equipment_group_{$faker->title}",
                        "model_description" => "model_description_{$faker->title}",
                        "header_brand" => "header_brand_{$faker->title}",
                        "header_model" => "header_model_{$faker->title}",
                        "serial_number_header" => "serial_number_heade_{$faker->title}",
                        "machine_serial_number" => "machine_serial_number_{$faker->title}",
                        "trailed_equipment_type" => "trailed_equipment_type_{$faker->title}",
                    ]
                ],
                "location_lat" => "23,325423235",
                "location_long" => "12,324532233",
                "location_country" => "UK",
                "location_city" => "Riga",
                "location_region" => "some region",
                "location_zipcode" => "32523",
                "location_street" => "street",
                "assignment" => "some text",
                "result" => "some text",
            ];

            $this->reportService->create($data, $this->getUserPs());
        }
    }

    /**
     * @throws Exception
     */
    public function getDealer()
    {
        if(!$dealerCount = Dealer::count()){
            throw new Exception('Таблица дилеров пуста');
        }

        return Dealer::inRandomOrder()->first();
    }

    /**
     * @throws Exception
     */
    public function getClient()
    {
        if(!$clientCount = Client::count()){
            throw new Exception('Таблица клиентов пуста');
        }

        return Client::inRandomOrder()->first();
    }

    /**
     * @throws Exception
     */
    public function getModelDescription()
    {
        if(!$mdCount = ModelDescription::count()){
            throw new Exception('Таблица model_description пуста');
        }

        return ModelDescription::inRandomOrder()->first();
    }

    /**
     * @throws Exception
     */
    public function getEquipmentGroup()
    {
        if(!$eqCount = EquipmentGroup::count()){
            throw new Exception('Таблица model_description пуста');
        }

        return EquipmentGroup::inRandomOrder()->first();
    }

    /**
     * @throws Exception
     */
    public function getUserPs()
    {
        if(!$eqCount = User::query()->ps()->count()){
            throw new Exception('Нет ps');
        }

        return User::query()->ps()
            ->inRandomOrder()->first();
    }

}
