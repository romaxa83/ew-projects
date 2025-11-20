<?php

namespace WezomCms\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Foundation\Buttons\ButtonsMaker;
use WezomCms\Core\Foundation\Buttons\RestoreTrashed;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;

/**
 * Trait ActionShowTrait
 * @package WezomCms\Core\Traits
 * @mixin AbstractCRUDController
 */
trait ActionShowTrait
{
    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        /** @var Model|SoftDeletes $obj */
        $obj = $this->model()::query()
            ->when(method_exists($this, 'softDeleteEnabled') && $this->softDeleteEnabled(), function ($query) {
                $query->withTrashed();
            })->find($id);

        if ($response = $this->redirectIfNoRecord($obj, $this->makeRouteName('index'), $this->abilityPrefix() . '.view')) {
            return $response;
        }

        $this->authorizeForAction('show', $obj);

        $this->addBreadcrumb($this->title());
        if (method_exists($this, 'softDeleteEnabled') && $this->softDeleteEnabled() && $obj->trashed()) {
            $this->addBreadcrumb(__('cms-core::admin.layout.Deleted records'));
        }

        $title = $this->showTitle($obj);
        $this->addBreadcrumb($title);
        $this->pageName->setPageName($title);

        $this->showButtons($obj);

        $data = [
            'obj' => $obj,
            'locales' => app('locales'),
            'viewPath' => $this->view,
            'routeName' => $this->routeName,
        ];

        if (method_exists($this, 'showViewData')) {
            $data = array_merge($data, $this->showViewData($obj, $data));
        } elseif (method_exists($this, 'formData')) {
            $data = array_merge($data, $this->formData($obj, $data));
        }

        event('crud:show:' . $this->model(), compact('obj', 'data'));

        return \View::first([$this->view . '.show-wrapper', 'cms-core::admin.crud.show-wrapper'], $data);
    }

    /**
     * @param  Model  $model
     * @return string
     */
    protected function showTitle(Model $model): string
    {
        return $this->title() . ': ' . __('cms-core::admin.layout.Browsing');
    }

    /**
     * Register show buttons.
     *
     * @param  Model|SoftDeletes  $model
     * @return ButtonsContainerInterface
     */
    protected function showButtons(Model $model)
    {
        /** @var ButtonsContainerInterface $buttons */
        $buttons = app(ButtonsContainerInterface::class);

        $isTrashed = method_exists($this, 'softDeleteEnabled') && $this->softDeleteEnabled() && $model->trashed();

        // Close
        if ($this->allowsForAction('view', $this->model())) {
            $buttons->add(ButtonsMaker::close(route($this->makeRouteName($isTrashed ? 'trashed' : 'index'))));
        }

        // Restore
        if ($isTrashed) {
            $buttons->add(RestoreTrashed::make()
                ->setModel($model)
                ->setRouteName($this->makeRouteName('restore')));
        }

        foreach (array_filter(event($this->abilityPrefix() . ':show_buttons')) as $eventButtons) {
            if (is_iterable($eventButtons)) {
                foreach ($eventButtons as $button) {
                    $buttons->add($button);
                }
            } else {
                $buttons->add($eventButtons);
            }
        }

        $this->addFrontUrlButton($buttons, $model);

        return $buttons;
    }
}
