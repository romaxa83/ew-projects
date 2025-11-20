<?php

namespace WezomCms\Core\Tests\Foundation;

use Tests\TestCase;
use WezomCms\Core\Foundation\Breadcrumbs;

class BreadcrumbsTest extends TestCase
{
    /**
     * @var Breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->breadcrumbs = new Breadcrumbs();
    }

    public function testAddItemWithLink()
    {
        $this->breadcrumbs->add('foo', 'bar');

        $actual = $this->breadcrumbs->getBreadcrumbs()[0];

        $expected = ['name' => 'foo', 'link' => 'bar'];

        $this->assertEquals($expected, $actual);
    }

    public function testAddItemWithoutLink()
    {
        $this->breadcrumbs->add('foo');

        $actual = $this->breadcrumbs->getBreadcrumbs()[0];

        $expected = ['name' => 'foo', 'link' => null];

        $this->assertEquals($expected, $actual);
    }
}
