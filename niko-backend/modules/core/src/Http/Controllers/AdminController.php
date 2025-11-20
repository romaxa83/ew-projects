<?php

namespace WezomCms\Core\Http\Controllers;

use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Route;
use JsValidator;
use WezomCms\Core\Contracts\AdminPageNameInterface;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Contracts\ButtonInterface;
use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Foundation\Buttons\ButtonsMaker;
use WezomCms\Core\Foundation\Buttons\DropDownLinks;
use WezomCms\Core\Foundation\Buttons\GoToInnerPage;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Traits\BreadcrumbsTrait;

class AdminController extends BaseController
{
    use AuthorizesRequests;
    use BreadcrumbsTrait;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @var AssetManagerInterface
     */
    protected $assets;

    /**
     * @var AdminPageNameInterface
     */
    protected $pageName;

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->assets = app(AssetManagerInterface::class);
        $this->pageName = app(AdminPageNameInterface::class);
    }

    /**
     * @param  Request  $request
     * @param  string  $configSelector
     * @return int
     */
    protected function getLimit(Request $request, string $configSelector = 'admin.limit'): int
    {
        if ((int) $request->get('per_page') > 0) {
            return (int) $request->get('per_page');
        }

        return $this->getDefaultLimit($configSelector);
    }

    /**
     * Generate list for per-page select.
     *
     * @return array
     */
    protected function perPageList(): array
    {
        $limit = $this->getDefaultLimit();

        $result = [];
        for ($i = 7; $i > 0; $i--) {
            $result[$limit] = $limit;
            $limit *= 2;
        }

        return $result;
    }

    /**
     * @param  string  $configSelector
     * @return int
     */
    protected function getDefaultLimit(string $configSelector = 'admin.limit'): int
    {
        $defaultAdminLimit = config('cms.core.main.admin_limit');

        if (method_exists($this, 'controllerBaseName')) {
            return (int) settings($this->controllerBaseName() . '.' . $configSelector, $defaultAdminLimit);
        } else {
            return (int) $defaultAdminLimit;
        }
    }

    /**
     * @param  mixed|null  $model
     * @param  array  $parameters
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectAfterSave($model = null, array $parameters = [])
    {
        $action = app('request')->get('form-action');

        $parameters = array_merge($parameters, $this->addParametersToRedirect($action));

        $baseRouteName = Helpers::getBaseRouteName();
        switch ($action) {
            case ButtonInterface::ACTION_SAVE_AND_CREATE:
                if (isset($parameters[0])) {
                    unset($parameters[0]); // Remove just created id.
                }

                return redirect()->route($baseRouteName . '.create', $parameters);
                break;
            case ButtonInterface::ACTION_SAVE_AND_CLOSE:
                if (isset($parameters[0])) {
                    unset($parameters[0]); // Remove just created id.
                }

                return redirect($this->listRoute($baseRouteName, $parameters));
                break;
            case ButtonInterface::ACTION_SAVE:
            default:
                if (
                    ButtonInterface::ACTION_STORE === app(Route::class)->getActionMethod()
                    && $model
                    && $this->allowsForAction('edit', $model)
                ) {
                    return redirect()->route($baseRouteName . '.edit', $parameters);
                }

                if (isset($parameters[0])) {
                    unset($parameters[0]); // Remove just created id.
                }

                return redirect()->back()->withInput($parameters);
                break;
        }
    }

    /**
     * @param $baseRouteName
     * @param  array  $parameters
     * @return string
     */
    protected function listRoute($baseRouteName, array $parameters = [])
    {
        $redirectUrl = app('request')->get('redirect-url');

        return $redirectUrl ?: route($baseRouteName . '.index', $parameters);
    }

    /**
     * @param $formRequest
     * @param  string  $formSelector
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function renderJsValidator($formRequest, $formSelector = '#form')
    {
        $this->assets->addInlineScript(JsValidator::formRequest($formRequest, $formSelector));
    }

    /**
     * @param $action
     * @return array
     */
    protected function addParametersToRedirect($action): array
    {
        return [];
    }

    /**
     * @return null|string
     */
    protected function abilityPrefix(): ?string
    {
        return null;
    }

    /**
     * @param $ability
     * @param  array|null  $arguments
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeForAction($ability, ...$arguments)
    {
        $abilityPrefix = $this->abilityPrefix();

        if ($abilityPrefix != null) {
            $this->authorize($abilityPrefix . '.' . $ability, $arguments);
        }
    }

    /**
     * @param $ability
     * @param  array|null  $arguments
     * @return bool
     */
    protected function allowsForAction($ability, ...$arguments)
    {
        $abilityPrefix = $this->abilityPrefix();

        if ($abilityPrefix != null) {
            return Gate::allows($abilityPrefix . '.' . $ability, $arguments);
        }

        return true;
    }

    /**
     * Register index buttons.
     *
     * @return ButtonsContainerInterface
     */
    protected function indexButtons()
    {
        $hasSettings = method_exists($this, 'settings') && count($this->settings());

        $buttons = ButtonsMaker::indexButtons(
            $this->routeName,
            $this->abilityPrefix(),
            $hasSettings,
            method_exists($this, 'softDeleteEnabled') && $this->softDeleteEnabled(),
            method_exists($this, 'buildTrashedTitle') ? $this->buildTrashedTitle() : null
        );

        foreach (array_filter(event($this->abilityPrefix() . ':index_buttons')) as $eventButtons) {
            if (is_iterable($eventButtons)) {
                foreach ($eventButtons as $button) {
                    $buttons->add($button);
                }
            } else {
                $buttons->add($eventButtons);
            }
        }

        $this->addGoToSiteButton($buttons);

        return $buttons;
    }

    /**
     * Register form buttons.
     *
     * @param  string  $currentAction
     * @param $model
     * @param  string|null  $index
     * @param  string|null  $indexAbility
     * @return ButtonsContainerInterface
     */
    protected function formButtons(string $currentAction, $model, string $index = null, string $indexAbility = null)
    {
        $buttons = ButtonsMaker::formButtons(
            $currentAction,
            $this->routeName,
            $this->abilityPrefix(),
            $model,
            $index,
            $indexAbility
        );

        $events = array_filter(event($this->abilityPrefix() . ':form_buttons', compact('currentAction', 'model')));
        foreach ($events as $eventButtons) {
            if (is_iterable($eventButtons)) {
                foreach ($eventButtons as $button) {
                    $buttons->add($button);
                }
            } else {
                $buttons->add($eventButtons);
            }
        }

        if (is_object($model)) {
            $this->addFrontUrlButton($buttons, $model);
        }

        return $buttons;
    }

    /**
     * @param  ButtonsContainerInterface  $buttons
     */
    protected function addGoToSiteButton(ButtonsContainerInterface $buttons)
    {
        $frontUrl = $this->frontUrl();
        if (!$frontUrl) {
            return;
        }

        $links = [];
        foreach (app('locales') as $locale => $language) {
            $links[$language] = \LaravelLocalization::getLocalizedURL($locale, $frontUrl);
        }

        $dropDown = DropDownLinks::make()
            ->setIcon('fa-external-link')
            ->setTitle(__('cms-core::admin.layout.Go to the website'))
            ->setLinks($links)
            ->setSortPosition(10);

        $buttons->add($dropDown);
    }

    /**
     * @return string|null
     */
    protected function frontUrl(): ?string
    {
        return null;
    }


    /**
     * @param  Model|null  $obj
     * @param  string  $route
     * @param  string  $ability
     * @return \Illuminate\Http\RedirectResponse|void
     */
    protected function redirectIfNoRecord($obj, $route, $ability)
    {
        if (!$obj) {
            if (Gate::allows($ability)) {
                flash(__('cms-core::admin.layout.Record not found or was deleted'), 'error');
                return redirect()->route($route);
            } else {
                return abort(404);
            }
        }
    }

    /**
     * @param  ButtonsContainerInterface  $buttons
     * @param $model
     */
    protected function addFrontUrlButton(ButtonsContainerInterface $buttons, $model)
    {
        if (method_exists($model, 'getFrontUrl')) {
            $buttons->add(GoToInnerPage::make()
                ->setIcon('fa-external-link')
                ->setTitle(__('cms-core::admin.layout.Go to the website'))
                ->setModel($model)
                ->setSortPosition(10));
        }
    }
}
