<?php

namespace Tests\Unit\Models\Import;

use App\Models\Import\IosLinkImport;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Builder\UserBuilder;

class IosLinkImportTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function check_is_new(): void
    {
        /** @var $user User */
        $user = $this->userBuilder->create();

        /** @var $import IosLinkImport */
        $import = IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_NEW,
        ]);

        $this->assertTrue($import->isNew());
        $this->assertFalse($import->isInProcess());
        $this->assertFalse($import->isFailed());
        $this->assertFalse($import->isDone());
    }

    /** @test */
    public function check_is_in_process(): void
    {
        /** @var $user User */
        $user = $this->userBuilder->create();

        /** @var $import IosLinkImport */
        $import = IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_IN_PROCESS,
        ]);

        $this->assertFalse($import->isNew());
        $this->assertTrue($import->isInProcess());
        $this->assertFalse($import->isFailed());
        $this->assertFalse($import->isDone());
    }

    /** @test */
    public function check_is_failed(): void
    {
        /** @var $user User */
        $user = $this->userBuilder->create();

        /** @var $import IosLinkImport */
        $import = IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_FAILED,
        ]);

        $this->assertFalse($import->isNew());
        $this->assertFalse($import->isInProcess());
        $this->assertTrue($import->isFailed());
        $this->assertFalse($import->isDone());
    }

    /** @test */
    public function check_is_done(): void
    {
        /** @var $user User */
        $user = $this->userBuilder->create();

        /** @var $import IosLinkImport */
        $import = IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_DONE,
        ]);

        $this->assertFalse($import->isNew());
        $this->assertFalse($import->isInProcess());
        $this->assertFalse($import->isFailed());
        $this->assertTrue($import->isDone());
    }
}

