<?php

namespace Tests\Feature\Partners;

use App\Models\Division;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Partners\PartnerBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class AjaxInfoTest extends TestCase
{
    use DatabaseTransactions;
    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected PartnerBuilder $partnerBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->partnerBuilder = resolve(PartnerBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $user = $this->userBuilder->create();
        $this->loginUser($user);
        $this->session(['division' => $division->toArray()]);

        /** @var $model Partner */
        $model = $this->partnerBuilder->division($division)->create();

        $this->post(route('partner.info', ['id' => $model->id]))
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'division_id' => $model->division_id,
                    'contact_person' => $model->contact_person,
                    'phone' => $model->phone,
                    'email' => $model->email,
                ],
                'divisions' => [
                    $division->id => [
                        'id' => $division->id,
                        'title' => $division->title,
                    ]
                ],
            ])
            ->assertJsonCount(1, 'divisions')
        ;
    }

    /** @test */
    public function nor_found_record()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $user = $this->userBuilder->create();
        $this->loginUser($user);
        $this->session(['division' => $division]);

        /** @var $model Partner */
        $model = $this->partnerBuilder->division($division)->create();

        $this->post(route('partner.info', ['id' => $model->id +1]))
            ->assertJson([
                'success' => true,
                'record' => null,
                'divisions' => [
                    $division->id => [
                        'id' => $division->id,
                        'title' => $division->title,
                    ]
                ],
            ])
            ->assertJsonCount(1, 'divisions')
        ;
    }
}



