<?php

namespace WezomCms\Core\Foundation\Dashboard;

use WezomCms\Core\Contracts\DashboardWidgetInterface;

abstract class AbstractDashboardWidget implements DashboardWidgetInterface
{
    /**
     * size int - add count column md
     * size arr add class and column $size = ['sm' => 2,'md' => 5];
     * @var int
     */
    protected $size = 3;

    /**
     * @return string
     */
    abstract public function render();

    /**
     * @return string
     */
    public function toHtml()
    {
        return '<div class="' . $this->buildSizeClasses() . ' ' . $this->getClasses() . '">'
            . $this->render() . '</div>';
    }

    /**
     * @return string
     */
    public function getClasses(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function buildSizeClasses(): string
    {
        if (is_array($this->size)) {
            $styles = [];
            foreach ($this->size as $position => $size) {
                $styles[] = sprintf('col-%s-%d', $position, $size);
            }

            return implode(' ', $styles);
        } else {
            return 'col-xs-12 col-sm-6 col-md-4 col-lg-' . $this->size . ' mb-2 mb-lg-3';
        }
    }
}
