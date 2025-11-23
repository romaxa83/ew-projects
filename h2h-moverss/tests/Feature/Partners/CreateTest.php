<?php

namespace Tests\Feature\Partners;

use App\Models\Division;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);

        parent::setUp();

        $this->data = [
            "name" => "Cool partner name",
            "contact_person" => "John Doe",
            "phone" => "380441232323",
            "email" => "test@gmail.com",
        ];
    }

    /** @test */
    public function success()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        $user = $this->userBuilder->create();

        $this->loginUser($user);

        $this->session(['division' => $division]);

        $data = $this->data;

        $this->assertEquals(0, Partner::count());

        $this->post(route('partner.create'), $data)
            ->assertStatus(302)
        ;

        $this->assertEquals(1, Partner::count());

        /** @var $model Partner */
        $model = Partner::first();

        $this->assertEquals($model->name, $data['name']);
        $this->assertEquals($model->division_id, $division->id);
        $this->assertEquals($model->contact_person, $data['contact_person']);
        $this->assertEquals($model->phone, $data['phone']);
        $this->assertEquals($model->email, $data['email']);

    }
}
