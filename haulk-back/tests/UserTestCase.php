<?php

namespace Tests;

use App\Repositories\Roles\RoleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class UserTestCase extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var RoleRepository
     */
    protected $roleRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleRepository = resolve(RoleRepository::class);
    }
}
