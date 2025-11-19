<?php

namespace Wezom\Core\Tests\Feature\Testing;

use LogicException;
use PHPUnit\Framework\ExpectationFailedException;
use Wezom\Core\Models\Permission\Permission;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Testing\TestCase;

class QueryInterceptorTest extends TestCase
{
    public function testQueryInterceptor(): void
    {
        Role::factory()->has(Permission::factory())->create();

        $this->startQueryCount();

        Role::query()->first();
        //select from roles
        $this->assertQueryCount(1);

        //one query from previous and two queries there
        Role::query()->with('permissions')->first();
        $this->assertQueryCount(3);

        //reset counter
        $this->startQueryCount();

        Role::query()->with('permissions')->first();
        $this->assertQueryCount(2);
    }

    public function testFailedMessage(): void
    {
        $message = <<<INTERCEPTOR_MESSAGE
~Expected 0 queries to be called, but there are 2!
List of all executed queries:
Query 1 \(took: \d+ms\):
select \* from "roles" where "roles"\."id" = 0 limit 1
Query 2 \(took: \d+ms\):
select \* from "roles" where "roles"\."id" = 0 limit 1~
INTERCEPTOR_MESSAGE;

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageMatches($message);

        $this->startQueryCount();
        Role::query()->whereKey(0)->first();
        Role::query()->whereKey(0)->first();
        $this->assertQueryCount(0);
    }

    public function testThrowsWhenAssertingWithoutQueryLogging(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Query logging must be started in order to count queries!');

        Role::factory()->has(Permission::factory())->create();
        $this->assertQueryCount(0);
    }
}
