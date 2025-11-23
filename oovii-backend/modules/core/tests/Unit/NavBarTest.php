<?php

namespace WezomCms\Core\Tests\Unit;

use Tests\TestCase;
use WezomCms\Core\Foundation\NavBar\AbstractNavBarItem;
use WezomCms\Core\Foundation\NavBar\NavBar;

class NavBarTest extends TestCase
{
    /**
     * @var NavBar
     */
    protected $navBar;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->navBar = new NavBar();
    }

    public function testAddItem()
    {
        $item = $this->generateItem();

        $this->navBar->add($item);

        $savedItem = $this->navBar->getAllItems()[0];

        $this->assertEquals($item, $savedItem);
    }

    public function testToHtml()
    {
        $item = $this->generateItem();

        $this->navBar->add($item);

        $html = $this->navBar->toHtml();

        $this->assertEquals($item->toHtml(), $html);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|AbstractNavBarItem
     * @throws \ReflectionException
     */
    private function generateItem()
    {
        $item = $this->getMockForAbstractClass(AbstractNavBarItem::class);

        $item->expects($this->any())
            ->method('render')
            ->willReturn('content');

        return $item;
    }
}
