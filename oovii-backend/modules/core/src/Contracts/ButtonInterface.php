<?php

namespace WezomCms\Core\Contracts;

use Illuminate\Contracts\Support\Htmlable;
use Stringable;

interface ButtonInterface extends Htmlable, Stringable
{
    public const TYPE_BUTTON = 'button';
    public const TYPE_LINK = 'link';

    public const ACTION_SAVE_AND_CREATE = 'save-and-create';
    public const ACTION_SAVE_AND_CLOSE = 'save-and-close';
    public const ACTION_SAVE = 'save';
    public const ACTION_STORE = 'store';

    /**
     * @param  int  $sort
     * @return ButtonInterface
     */
    public function setSortPosition(int $sort): ButtonInterface;

    /**
     * Get sort position.
     *
     * @return int
     */
    public function getSortPosition(): int;

    /**
     * Set button type.
     *
     * @param  string  $type
     * @return ButtonInterface
     */
    public function type($type): ButtonInterface;

    /**
     * Get button type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set link for button type === 'link'.
     *
     * @param  string  $link
     * @return ButtonInterface
     */
    public function setLink(string $link): ButtonInterface;

    /**
     * Get link for button type === 'link'.
     *
     * @return string|null
     */
    public function getLink(): ?string;

    /**
     * Set gate ability.
     *
     * @param  string  $ability
     * @return ButtonInterface
     */
    public function setAbility(string $ability): ButtonInterface;

    /**
     * Get gate ability.
     *
     * @return ButtonInterface
     */
    public function getAbility(): ?string;

    /**
     * Set button or link name.
     *
     * @param  string  $name
     * @return ButtonInterface
     */
    public function setName($name): ButtonInterface;

    /**
     * Get button or link name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set title.
     *
     * @param  string  $title
     * @return ButtonInterface
     */
    public function setTitle(string $title): ButtonInterface;

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @param  string|array  $classes
     * @return ButtonInterface
     */
    public function setClass($classes): ButtonInterface;

    /**
     * @return array
     */
    public function getClasses(): array;

    /**
     * @param $name
     * @param $value
     * @return ButtonInterface
     */
    public function setAttribute($name, $value): ButtonInterface;

    /**
     * @param  iterable  $attributes
     * @return ButtonInterface
     */
    public function setAttributes(iterable $attributes): ButtonInterface;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @return string
     */
    public function buildAttributes(): string;

    /**
     * @param  string  $iconClass
     * @return ButtonInterface
     */
    public function setIcon(string $iconClass): ButtonInterface;

    /**
     * @return string|null
     */
    public function getIcon(): ?string;

    /**
     * Set view path.
     *
     * @param  string  $view
     * @return ButtonInterface
     */
    public function view(string $view): ButtonInterface;

    /**
     * Get view path.
     *
     * @return string
     */
    public function getView(): string;

    /**
     * Generate button or link.
     *
     * @return string|mixed
     */
    public function render();
}
