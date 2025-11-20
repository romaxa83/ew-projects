<?php

namespace WezomCms\Core\Foundation\Buttons;

use WezomCms\Core\Contracts\ButtonInterface;

class DropDownLinks extends Link implements ButtonInterface
{
    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var array
     */
    protected $classes = ['btn', 'btn-sm', 'btn-outline-secondary'];

    /**
     * @var array
     */
    protected $attributes = ['target' => '_blank'];

    /**
     * View path.
     *
     * @var string
     */
    protected $view = 'cms-core::admin.partials.buttons.drop-down-links';

    /**
     * Generate button or link.
     *
     * @return string|mixed
     */
    public function render()
    {
        if (empty($this->links)) {
            return '';
        }

        try {
            return view($this->getView(), ['link' => $this]);
        } catch (\Throwable $e) {
            logger($e->getMessage());

            return '';
        }
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param  array  $links
     * @return DropDownLinks
     */
    public function setLinks(array $links): DropDownLinks
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
