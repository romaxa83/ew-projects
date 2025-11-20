<?php

namespace WezomCms\Core\Tests\Unit;

use Illuminate\Routing\Route;
use Tests\TestCase;
use WezomCms\Core\Foundation\Helpers;

class HelpersTest extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        app('request')->setRouteResolver(function () {
            $route = new Route('GET', '', []);
            $route->bind(app('request'));
            $route->name('admin.foo');

            return $route;
        });
    }

    public function testGetCurrentController()
    {
        $expected = 'foo';

        $actual = Helpers::currentController();

        $this->assertEquals($expected, $actual);
    }

    public function testGetBaseRouteName()
    {
        $expected = 'admin';

        $actual = Helpers::getBaseRouteName();

        $this->assertEquals($expected, $actual);
    }
}
