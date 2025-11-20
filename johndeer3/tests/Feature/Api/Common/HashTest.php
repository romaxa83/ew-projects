<?php

namespace Tests\Feature\Api\Common;

use App\Models\Version;
use App\Repositories\JD\ClientRepository;
use App\Repositories\JD\DealersRepository;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\JD\ManufacturerRepository;
use App\Repositories\JD\ModelDescriptionRepository;
use App\Repositories\PageRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class HashTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_client()
    {
        $hash = Version::getHash(app(ClientRepository::class)->getForHash());

        $this->getJson(route('api.catalog.hash', ['type' => Version::CLIENTS]))
            ->assertJson($this->structureSuccessResponse($hash))
        ;
    }

    /** @test */
    public function success_dealers()
    {
        $hash = Version::getHash(app(DealersRepository::class)->getForHash());

        $this->getJson(route('api.catalog.hash', ['type' => Version::DEALERS]))
            ->assertJson($this->structureSuccessResponse($hash))
        ;
    }

    /** @test */
    public function success_model_description()
    {
        $hash = Version::getHash(app(ModelDescriptionRepository::class)->getForHash());

        $this->getJson(route('api.catalog.hash', ['type' => 'model-descriptions']))
            ->assertJson($this->structureSuccessResponse($hash))
        ;
    }

    /** @test */
    public function success_manufacturer()
    {
        $hash = Version::getHash(app(ManufacturerRepository::class)->getForHash());

        $this->getJson(route('api.catalog.hash', ['type' => Version::MANUFACTURER]))
            ->assertJson($this->structureSuccessResponse($hash))
        ;
    }

    /** @test */
    public function success_equipment_group()
    {
        $hash = Version::getHash(app(EquipmentGroupRepository::class)->getAllForHash());

        $this->getJson(route('api.catalog.hash', ['type' => 'equipment-groups']))
            ->assertJson($this->structureSuccessResponse($hash))
        ;
    }

    /** @test */
    public function success_page()
    {
        $hash = Version::getHash(app(PageRepository::class)->getForHash());

        $this->getJson(route('api.catalog.hash', ['type' => 'pages']))
            ->assertJson($this->structureSuccessResponse($hash))
        ;
    }

    /** @test */
    public function fail_wrong_hash()
    {
        $this->getJson(route('api.catalog.hash', ['type' => 'wrong']))
            ->assertJson($this->structureErrorResponse(__("message.exceptions.not implement getting hash data", [
                "type" => 'wrong'
            ])))
        ;
    }
}
