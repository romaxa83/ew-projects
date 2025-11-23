<?php

namespace WezomCms\Core\Foundation\Buttons;

use Gate;
use WezomCms\Core\Contracts\ButtonInterface;
use WezomCms\Core\Contracts\ButtonsContainerInterface;

class ButtonsMaker
{
    /**
     * @param  string  $baseRoute
     * @param  string  $abilityPrefix
     * @param  bool  $hasSettings
     * @param  bool  $hasTrashed
     * @param  string|null  $trashedText
     * @return ButtonsContainerInterface
     */
    public static function indexButtons(
        string $baseRoute,
        string $abilityPrefix,
        bool $hasSettings = false,
        bool $hasTrashed = false,
        ?string $trashedText = null
    ): ButtonsContainerInterface {
        /** @var ButtonsContainerInterface $container */
        $container = app(ButtonsContainerInterface::class);

        if (Gate::allows($abilityPrefix . '.create')) {
            $container->add(ButtonsMaker::createLink(route($baseRoute . '.create')));
        }

        if ($hasTrashed) {
            $container->add(Link::make()
                ->setLink(route($baseRoute . '.trashed'))
                ->setSortPosition(2)
                ->setName($trashedText ? : __('cms-core::admin.layout.Deleted records'))
                ->setICon('fa-trash')
                ->setClass(['btn-sm', 'btn-info']));
        }

        if ($hasSettings && Gate::check($abilityPrefix . '.edit-settings')) {
            $container->add(ButtonsMaker::editSettings(route($baseRoute . '.settings')));
        }

        return $container;
    }

    /**
     * @param  string  $currentAction
     * @param  string  $baseRoute
     * @param  string  $abilityPrefix
     * @param  null  $model
     * @param  string|null  $index
     * @param  string|null  $indexAbility
     * @return ButtonsContainerInterface
     */
    public static function formButtons(
        string $currentAction,
        string $baseRoute,
        string $abilityPrefix,
        $model = null,
        string $index = null,
        string $indexAbility = null
    ): ButtonsContainerInterface {
        $index = $index !== null ? $index : route($baseRoute . '.index');
        $indexAbility = $indexAbility !== null ? $indexAbility : $abilityPrefix . '.view';
        $currentAbility = $abilityPrefix . '.' . $currentAction;

        /** @var ButtonsContainerInterface $container */
        $container = app(ButtonsContainerInterface::class);

        // Save
        if (Gate::allows($currentAbility, $model)) {
            $container->add(ButtonsMaker::save());
        }

        // Save & add
        if (Gate::allows($currentAbility, $model) && Gate::allows($abilityPrefix . '.create')) {
            $container->add(ButtonsMaker::saveAndCreate(route($baseRoute . '.create')));
        }

        // Save & close
        if (Gate::allows($currentAbility, $model) && Gate::allows($indexAbility)) {
            $container->add(ButtonsMaker::saveAndClose($index));
        }

        // Close
        if (Gate::allows($indexAbility)) {
            $container->add(ButtonsMaker::close($index));
        }

        return $container;
    }

    /**
     * @param $link
     * @param  array  $attributes
     * @return \WezomCms\Core\Contracts\ButtonInterface|null
     */
    public static function createLink($link, array $attributes = [])
    {
        return Link::make()
            ->setName(__('cms-core::admin.buttons.Create'))
            ->setTitle(__('cms-core::admin.buttons.Create new resource'))
            ->setLink($link)
            ->setAttributes($attributes)
            ->setClass('btn-sm', 'btn-primary')
            ->setIcon('fa-plus-circle')
            ->setSortPosition(1);
    }

    /**
     * @param $link
     * @param  array  $attributes
     * @return \WezomCms\Core\Contracts\ButtonInterface|null
     */
    public static function editSettings($link, array $attributes = [])
    {
        return Link::make()
            ->setName(__('cms-core::admin.buttons.Settings'))
            ->setTitle(__('cms-core::admin.buttons.Edit module settings'))
            ->setLink($link)
            ->setAttributes($attributes)
            ->setClass('btn-sm', 'btn-outline-secondary')
            ->setIcon('fa-wrench')
            ->setSortPosition(5);
    }

    /**
     * @return \WezomCms\Core\Contracts\ButtonInterface
     */
    public static function save()
    {
        return Button::make()
            ->setName(__('cms-core::admin.buttons.Save'))
            ->setAttribute('data-action', ButtonInterface::ACTION_SAVE)
            ->setClass('btn-sm', 'btn-success', 'js-form-submit')
            ->setIcon('fa-save')
            ->setSortPosition(7);
    }

    /**
     * @param  string  $link
     * @return ButtonInterface
     */
    public static function saveAndCreate(string $link)
    {
        return Button::make()
            ->setName(__('cms-core::admin.buttons.Save and add'))
            ->setAttribute('data-action', ButtonInterface::ACTION_SAVE_AND_CREATE)
            ->setAttribute('data-redirect-url', $link)
            ->setClass('btn-sm', 'btn-primary', 'js-form-submit')
            ->setIcon('fa-plus')
            ->setSortPosition(8);
    }

    /**
     * @param  string  $link
     * @return ButtonInterface
     */
    public static function saveAndClose(string $link)
    {
        return Button::make()
            ->setName(__('cms-core::admin.buttons.Save and close'))
            ->setAttribute('data-action', ButtonInterface::ACTION_SAVE_AND_CLOSE)
            ->setAttribute('data-redirect-url', $link)
            ->setClass('btn-sm', 'btn-info', 'js-form-submit')
            ->setIcon('fa-sign-out')
            ->setSortPosition(9);
    }

    /**
     * @param $link
     * @param  array  $attributes
     * @return \WezomCms\Core\Contracts\ButtonInterface|null
     */
    public static function close($link, array $attributes = [])
    {
        return Link::make()
            ->setName(__('cms-core::admin.buttons.Close'))
            ->setLink($link)
            ->setAttributes($attributes)
            ->setClass('btn-sm btn-danger')
            ->setIcon('fa-times')
            ->setSortPosition(10);
    }
}
