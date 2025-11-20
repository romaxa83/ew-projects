<?php

namespace Tests\Feature\Api\Report\Create;

use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
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
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function validate_status()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->postJson(route('api.report.create'), [])
            ->assertJson($this->structureErrorResponse(['The status field is required.']))
        ;
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

        $this->postJson(route('api.report.create'), [
            'status' => ReportStatus::CREATED,
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
            ['assignment', 99, 'The assignment must be a string.'],
            ['client_comment', 99, 'The client comment must be a string.'],
            ['client_email', 99, 'The client email must be a string.'],
            ['clients', 'str', 'The clients must be an array.'],
            ['machines', 'str', 'The machines must be an array.'],
            ['location', 'str', 'The location must be an array.'],
            ['features', 'str', 'The features must be an array.'],
            ['result', 99, 'The result must be a string.'],
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

        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.create'), [
            'status' => ReportStatus::CREATED,
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
            ['client_id', 999999, 'The selected clients.0.client_id is invalid.'],
            ['type', 999999, 'The selected clients.0.type is invalid.'],
            ['company_name', 999999, 'The clients.0.company_name must be a string.'],
            ['customer_last_name', 999999, 'The clients.0.customer_last_name must be a string.'],
            ['customer_first_name', 999999, 'The clients.0.customer_first_name must be a string.'],
            ['customer_id', 999999, 'The clients.0.customer_id must be a string.'],
            ['comment', 999999, 'The clients.0.comment must be a string.'],
            ['quantity_machine', 'ss', 'The clients.0.quantity_machine must be an integer.'],
            ['model_description_id', 'ss', 'The clients.0.model_description_id must be an integer.'],
            ['model_description_id', 99999, 'The selected clients.0.model_description_id is invalid.'],
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

        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.create'), [
            'status' => ReportStatus::CREATED,
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
            ['equipment_group_id', 99999, 'The selected machines.0.equipment_group_id is invalid.'],
            ['model_description_id', 'ss', 'The machines.0.model_description_id must be an integer.'],
            ['model_description_id', 99999, 'The selected machines.0.model_description_id is invalid.'],
            ['header_brand_id', 'ss', 'The machines.0.header_brand_id must be an integer.'],
            ['header_brand_id', 99999, 'The selected machines.0.header_brand_id is invalid.'],
            ['header_model_id', 'ss', 'The machines.0.header_model_id must be an integer.'],
            ['header_model_id', 99999, 'The selected machines.0.header_model_id is invalid.'],
            ['serial_number_header', 99999, 'The machines.0.serial_number_header must be a string.'],
            ['machine_serial_number', 99999, 'The machines.0.machine_serial_number must be a string.'],
            ['trailed_equipment_type', 99999, 'The machines.0.trailed_equipment_type must be a string.'],
            ['trailer_model', 99999, 'The machines.0.trailer_model must be a string.'],
            ['sub_manufacturer_id', 'ss', 'The machines.0.sub_manufacturer_id must be an integer.'],
            ['sub_equipment_group_id', 'ss', 'The machines.0.sub_equipment_group_id must be an integer.'],
            ['sub_equipment_group_id', 99999, 'The selected machines.0.sub_equipment_group_id is invalid.'],
            ['sub_model_description_id', 'ss', 'The machines.0.sub_model_description_id must be an integer.'],
            ['sub_model_description_id', 99999, 'The selected machines.0.sub_model_description_id is invalid.'],
            ['sub_machine_serial_number', 99999, 'The machines.0.sub_machine_serial_number must be a string.'],
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

        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.create'), [
            'status' => ReportStatus::CREATED,
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
     * @dataProvider validate_feature
     */
    public function validate_feature_data($field, $value, $msg)
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $feature_1 = $this->featureBuilder->setValues('val')
            ->withTranslation()->create();

        $this->postJson(route('api.report.create'), [
            'status' => ReportStatus::CREATED,
            'features' => [
                [
                    'id' => $feature_1->id,
                    'group' => [
                        [
                            $field => $value
                        ]
                    ],
                ]
            ]
        ])
            ->assertJson($this->structureErrorResponse([
                $msg
            ]))
        ;
    }

    public function validate_feature(): array
    {
        return [
            ['id', 'ss', 'The features.0.group.0.id must be an integer.'],
            ['name', 9999, 'The features.0.group.0.name must be a string.'],
            ['name', 'wrong', 'The selected features.0.group.0.name is invalid.'],
            ['value', 99, 'The features.0.group.0.value must be a string.'],
            ['choiceId', 'ss', 'The features.0.group.0.choiceId must be an integer.'],
            ['choiceId', 9999, 'The selected features.0.group.0.choiceId is invalid.'],
        ];
    }
}
