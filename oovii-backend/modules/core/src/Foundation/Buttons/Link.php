<?php

namespace WezomCms\Core\Foundation\Buttons;

use WezomCms\Core\Contracts\ButtonInterface;

class Link implements ButtonInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $link;

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var string|null
     */
    protected $ability;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var array
     */
    protected $classes = ['btn'];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string|null
     */
    protected $iconClass;

    /**
     * View path.
     *
     * @var string
     */
    protected $view = 'cms-core::admin.partials.buttons.link';

    /**
     * Button constructor.
     */
    public function __construct()
    {
        $this->type = ButtonInterface::TYPE_LINK;
    }

    /**
     * @return ButtonInterface
     */
    public static function make(): ButtonInterface
    {
        return new static();
    }

    /**
     * @param  int  $sort
     * @return ButtonInterface
     */
    public function setSortPosition(int $sort): ButtonInterface
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort position.
     *
     * @return int
     */
    public function getSortPosition(): int
    {
        return $this->sort;
    }

    /**
     * Set button type.
     *
     * @param  string  $type
     * @return ButtonInterface
     */
    public function type($type): ButtonInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get button type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set link for button type === 'link'.
     *
     * @param  string  $link
     * @return ButtonInterface
     */
    public function setLink(string $link): ButtonInterface
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link for button type === 'link'.
     *
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * Set gate ability.
     *
     * @param  string  $ability
     * @return ButtonInterface
     */
    public function setAbility(string $ability): ButtonInterface
    {
        $this->ability = $ability;

        return $this;
    }

    /**
     * Get gate ability.
     *
     * @return ButtonInterface
     */
    public function getAbility(): ?string
    {
        return $this->ability;
    }

    /**
     * Set button or link name.
     *
     * @param  string  $name
     * @return ButtonInterface
     */
    public function setName($name): ButtonInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get button or link name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set title.
     *
     * @param  string  $title
     * @return ButtonInterface
     */
    public function setTitle(string $title): ButtonInterface
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title ?: $this->getName();
    }

    /**
     * @param  string|array  $classes
     * @return ButtonInterface
     */
    public function setClass($classes): ButtonInterface
    {
        $classes = is_array($classes) ? $classes : func_get_args();

        $this->classes = array_merge($this->classes, $classes);

        return $this;
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param $name
     * @param $value
     * @return ButtonInterface
     */
    public function setAttribute($name, $value): ButtonInterface
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param  iterable  $attributes
     * @return ButtonInterface
     */
    public function setAttributes(iterable $attributes): ButtonInterface
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function buildAttributes(): string
    {
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            $attributes[] = $name . '="' . e(is_array($value) ? implode(' ', $value) : $value) . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * @param  string  $iconClass
     * @return ButtonInterface
     */
    public function setIcon(string $iconClass): ButtonInterface
    {
        $this->iconClass = $iconClass;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->iconClass;
    }

    /**
     * Set view path.
     *
     * @param  string  $view
     * @return ButtonInterface
     */
    public function view(string $view): ButtonInterface
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Get view path.
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * Generate button or link.
     *
     * @return string|mixed
     */
    public function render()
    {
        try {
            return view($this->getView(), ['link' => $this]);
        } catch (\Throwable $e) {
            logger($e->getMessage());

            return '';
        }
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
