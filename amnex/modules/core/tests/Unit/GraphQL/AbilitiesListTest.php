<?php

namespace Wezom\Core\Tests\Unit\GraphQL;

use Gate;
use Wezom\Core\GraphQL\Types\AbilitiesList;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Testing\TestCase;

class AbilitiesListTest extends TestCase
{
    public function testCachesSamePermissionCheck(): void
    {
        Gate::shouldReceive('check')
            ->once()
            ->with('roles.update')
            ->andReturn(true);
        Gate::shouldReceive('check')
            ->once()
            ->with('roles.delete')
            ->andReturn(false);

        foreach (Role::factory()->createMany(3) as $role) {
            $list = new AbilitiesList($role, 'roles', false);
            $this->assertTrue($list->update);
            $this->assertFalse($list->delete);
        }
    }
}
