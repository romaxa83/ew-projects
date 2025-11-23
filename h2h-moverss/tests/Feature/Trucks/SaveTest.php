<?php

namespace Tests\Feature\Trucks;

use App\Models\Division;
use App\Models\Partners\Partner;
use App\Models\Truck\Notes;
use App\Models\Truck\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Partners\PartnerBuilder;
use Tests\Builders\Trucks\NoteBuilder;
use Tests\Builders\Trucks\TruckBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class SaveTest extends TestCase
{
    use DatabaseTransactions;
    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected TruckBuilder $truckBuilder;
    protected NoteBuilder $noteBuilder;
    protected PartnerBuilder $partnerBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->noteBuilder = resolve(NoteBuilder::class);
        $this->partnerBuilder = resolve(PartnerBuilder::class);

        parent::setUp();

        $this->data = [];
    }

    /** @test */
    public function success_add_note()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division]);

        $user = $this->userBuilder->create();
        $this->loginUser($user);

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $data = $this->requestDataFromTruck($model);
        $data['notes'] = [
            [
                'id' => null,
                'value' => 'test msg'
            ]
        ];

        $this->assertEmpty($model->notes);

        $this->post(route('company.trucks.record.save', ['id' => $model->id]), $data)
            ->assertJson([
                'success' => true,
                'msg' => 'Truck changed',
                'record' => [
                    'id' => $model->id,
                ]
            ])
        ;

        $model->refresh();

        /** @var $note Notes */
        $note = $model->notes[0];

        $this->assertEquals($note->value, $data['notes'][0]['value']);
        $this->assertEquals($note->user_id, $user->id);
    }

    /** @test */
    public function success_add_note_more()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division]);

        $user = $this->userBuilder->create();
        $this->loginUser($user);

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        /** @var $note Notes */
        $note = $this->noteBuilder->truck($model)->create();

        $data = $this->requestDataFromTruck($model);
        $data['notes'] = [
            [
                'id' => $note->id,
                'value' => 'test msg 1'
            ],
            [
                'id' => null,
                'value' => 'test msg 2'
            ]
        ];

        $this->assertCount(1, $model->notes);

        $this->post(route('company.trucks.record.save', ['id' => $model->id]), $data)
            ->assertJson([
                'success' => true,
                'msg' => 'Truck changed',
                'record' => [
                    'id' => $model->id,
                ]
            ])
        ;

        $model->refresh();

        $this->assertCount(2, $model->notes);
    }

    /** @test */
    public function success_add_partner()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division]);

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        /** @var $partner Partner */
        $partner = $this->partnerBuilder->create();

        $data = $this->requestDataFromTruck($model);
        $data['partner_id'] = $partner->id;

        $this->assertNull($model->partner_id);

        $this->post(route('company.trucks.record.save', ['id' => $model->id]), $data)
            ->assertJson([
                'success' => true,
                'msg' => 'Truck changed',
                'record' => [
                    'id' => $model->id,
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->partner_id, $partner->id);
    }

    /** @test */
    public function success_update_partner()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division]);

        /** @var $partner Partner */
        $partner = $this->partnerBuilder->create();
        $partner_2 = $this->partnerBuilder->create();

        /** @var $model Truck */
        $model = $this->truckBuilder
            ->partner($partner_2)->create();

        $data = $this->requestDataFromTruck($model);
        $data['partner_id'] = $partner->id;

        $this->assertNotEquals($model->partner_id, $partner->id);

        $this->post(route('company.trucks.record.save', ['id' => $model->id]), $data)
            ->assertJson([
                'success' => true,
                'msg' => 'Truck changed',
                'record' => [
                    'id' => $model->id,
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->partner_id, $partner->id);
    }

    private function requestDataFromTruck(Truck $model): array
    {
        return [
            'id' => $model->id,
            'title' => $model->title,
            'active' => $model->active,
            'color' => $model->color,
            'l_plate' => $model->l_plate,
            'model' => $model->model,
            'nickname' => $model->nickname,
            'p_color' => $model->p_color,
            'vendor' => $model->vendor,
            'year' => $model->year,
            'vin' => $model->vin,
            'busy_weeks_days' => [
                'miscs' => []
            ],
            'notes' => [],
            'division_ids' => $model->division_ids,
        ];
    }
}
