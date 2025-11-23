<?php

namespace Tests\Feature\Partners;

use App\Models\Division;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Partners\PartnerBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected PartnerBuilder $partnerBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->partnerBuilder = resolve(PartnerBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        parent::setUp();

        $this->data = [
            "name" => "Cool partner name updated",
            "contact_person" => "John Doe updated",
            "phone" => "380441232323",
            "email" => "test.up@gmail.com",
        ];
    }

    /** @test */
    public function success()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division]);

        /** @var $model Partner */
        $model = $this->partnerBuilder->create();

        $data = $this->data;
        $data['division_id'] = $division->id;

        $this->assertNotEquals($model->name, $data['name']);
        $this->assertNotEquals($model->contact_person, $data['contact_person']);
        $this->assertNotEquals($model->phone, $data['phone']);
        $this->assertNotEquals($model->email, $data['email']);
        $this->assertNotEquals($model->division_id, $data['division_id']);

        $this->post(route('partner.update', ['id' => $model->id]), $data)
            ->assertJson([
                'success' => true,
                'msg' => "Partner changed",
                "record" => [
                    'id' => $model->id,
                    'name' => $data['name'],
                    'division_id' => $data['division_id'],
                    'contact_person' => $data['contact_person'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                ]
            ])
            ->assertStatus(200)
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division]);

        /** @var $model Partner */
        $model = $this->partnerBuilder->create();

        $data = $this->data;
        $data['division_id'] = $division->id;


        $this->post(route('partner.update', ['id' => $model->id + 1]), $data)
            ->assertJson([
                'success' => false,
            ])
            ->assertStatus(500)
        ;
    }
}
