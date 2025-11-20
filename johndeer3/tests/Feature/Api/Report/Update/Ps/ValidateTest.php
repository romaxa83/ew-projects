<?php

namespace Tests\Feature\Api\Report\Update\Ps;

use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ValidateTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /**
     * @test
     * @dataProvider validate
     */
    public function validate_main_data($field, $value, $msg)
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), [
            $field => $value
        ])
            ->assertJson($this->structureErrorResponse([$msg]))
        ;
    }

    public function validate(): array
    {
        return [
            ['salesman_name', 99999, 'The salesman_name must be a string.'],
            ['machine_for_compare', 99999, 'The machine for compare must be a string.'],
            ['clients', 'str', 'The clients must be an array.'],
            ['machines', 'str', 'The machines must be an array.'],
            ['location', 'str', 'The location must be an array.'],
            ['features', 'str', 'The features must be an array.'],
            ['assignment', 99, 'The assignment must be a string.'],
            ['result', 99, 'The result must be a string.'],
            ['client_comment', 99, 'The client comment must be a string.'],
        ];
    }

    /**
     * @test
     * @dataProvider validate_client
     */
    public function validate_client_data($field, $value, $msg)
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), [
            'clients' => [
                [$field => $value]
            ]
        ])
            ->assertJson($this->structureErrorResponse([$msg]))
        ;
    }

    public function validate_client(): array
    {
        return [
            ['client_id', 'ss', 'The clients.0.client_id must be an integer.'],
            ['client_id', 99999, 'The selected clients.0.client_id is invalid.'],
            ['type', 99999, 'The selected clients.0.type is invalid.'],
            ['company_name', 99999, 'The clients.0.company_name must be a string.'],
            ['customer_last_name', 99999, 'The clients.0.customer_last_name must be a string.'],
            ['customer_first_name', 99999, 'The clients.0.customer_first_name must be a string.'],
            ['customer_id', 99999, 'The clients.0.customer_id must be a string.'],
            ['comment', 99999, 'The clients.0.comment must be a string.'],
            ['quantity_machine', 'ss', 'The clients.0.quantity_machine must be an integer.'],
            ['model_description_id', 'ss', 'The clients.0.model_description_id must be an integer.'],
        ];
    }

    /**
     * @test
     * @dataProvider validate_machine
     */
    public function validate_machine_data($field, $value, $msg)
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep_1]), [
            'machines' => [
                [$field => $value]
            ]
        ])
            ->assertJson($this->structureErrorResponse([$msg]))
        ;
    }

    public function validate_machine(): array
    {
        return [
            ['manufacturer_id', 'ss', 'The machines.0.manufacturer_id must be an integer.'],
            ['equipment_group_id', 'ss', 'The machines.0.equipment_group_id must be an integer.'],
            ['equipment_group_id', 9999, 'The selected machines.0.equipment_group_id is invalid.'],
            ['model_description_id', 'ss', 'The machines.0.model_description_id must be an integer.'],
//            ['model_description_id', 99999999, 'The selected machines.0.model_description_id is invalid.'],
            ['header_brand_id', 'ss', 'The machines.0.header_brand_id must be an integer.'],
//            ['header_brand_id', 9999, 'The selected machines.0.header_brand_id is invalid.'],
            ['header_model_id', 'ss', 'The machines.0.header_model_id must be an integer.'],
//            ['header_model_id', 9999, 'The selected machines.0.header_model_id is invalid.'],
            ['serial_number_header', 9999, 'The machines.0.serial_number_header must be a string.'],
            ['machine_serial_number', 9999, 'The machines.0.machine_serial_number must be a string.'],
            ['trailed_equipment_type', 9999, 'The machines.0.trailed_equipment_type must be a string.'],
            ['trailer_model', 9999, 'The machines.0.trailer_model must be a string.'],
            ['sub_manufacturer_id', 'ss', 'The machines.0.sub_manufacturer_id must be an integer.'],
            ['sub_equipment_group_id', 'ss', 'The machines.0.sub_equipment_group_id must be an integer.'],
            ['sub_equipment_group_id', 9999, 'The selected machines.0.sub_equipment_group_id is invalid.'],
            ['sub_model_description_id', 'ss', 'The machines.0.sub_model_description_id must be an integer.'],
//            ['sub_model_description_id', 9999, 'The selected machines.0.sub_model_description_id is invalid.'],
            ['sub_machine_serial_number', 9999, 'The machines.0.sub_machine_serial_number must be a string.'],

        ];
    }

    /**
     * @test
     * @dataProvider validate_location
     */
    public function validate_location_data($field, $value, $msg)
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), [
            'location' => [
                $field => $value
            ]
        ])
            ->assertJson($this->structureErrorResponse([$msg]))
        ;
    }

    public function validate_location(): array
    {
        return [
            ['location_lat', 99, 'The location.location lat must be a string.'],
            ['location_long', 99, 'The location.location long must be a string.'],
            ['location_country', 99, 'The location.location country must be a string.'],
            ['location_city', 99, 'The location.location city must be a string.'],
            ['location_region', 99, 'The location.location region must be a string.'],
            ['location_zipcode', 99, 'The location.location zipcode must be a string.'],
            ['location_street', 99, 'The location.location street must be a string.'],
            ['location_district', 99, 'The location.location district must be a string.'],
        ];
    }

    /**
     * @test
     */
    public function validate_feature_data()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $rep = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.update.ps', ['report' => $rep]), [
            'features' => [
                [
                    'id' => null,
                    'value' => null,
                ]
            ]
        ])
            ->assertJson($this->structureErrorResponse([
                'The features.0.id field is required.',
            ]))
        ;
    }
}

