<?php

namespace WezomCms\Core\Tests\Unit;

use Tests\TestCase;
use WezomCms\Core\Foundation\Dashboard\AbstractDashboardWidget;
use WezomCms\Core\Foundation\Dashboard\DashboardContainer;

class DashboardWidgetsTest extends TestCase
{
    /**
     * @var DashboardContainer
     */
    private $container;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new DashboardContainer();
    }

    public function testAddAndGetWidget()
    {
        $widget = $this->getMockForAbstractClass(AbstractDashboardWidget::class);
        $widget->expects($this->any())
            ->method('render')
            ->will($this->returnValue('foo'));

        $this->container->addWidget($widget);

        $widgetFromContainer = $this->container->getWidgets()[0];

        $this->assertEquals($widget, $widgetFromContainer);

        $this->assertStringContainsString('foo', $widgetFromContainer->toHtml());
    }
}
