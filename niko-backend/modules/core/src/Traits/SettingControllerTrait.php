<?php

namespace WezomCms\Core\Traits;

use Illuminate\Http\Request;
use JsValidator;
use Route;
use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Foundation\Buttons\ButtonsMaker;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Http\Controllers\SingleSettingsController;
use WezomCms\Core\Models\Setting;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MultilingualGroup;

/**
 * Trait SettingControllerTrait
 * @package WezomCms\Core\Traits
 * @mixin AdminController
 */
trait SettingControllerTrait
{
    /**
     * Determine need clear cache after setup default values.
     *
     * @var bool
     */
    protected $needClearCache = false;

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function settingsForm()
    {
        $this->authorizeForAction('edit-settings');

        $this->before();

        $indexRoute = $this->indexRoute();

        /** @var ButtonsContainerInterface $buttonsContainer */
        $buttonsContainer = app(ButtonsContainerInterface::class);
        $buttonsContainer->add(ButtonsMaker::save());

        if ($this->allowsForAction('view')) {
            $buttonsContainer->add(ButtonsMaker::saveAndClose($indexRoute));
            $buttonsContainer->add(ButtonsMaker::close($indexRoute));
        }

        $this->addGoToSiteButton(app(ButtonsContainerInterface::class));

        $this->setMeta();

        $fields = $this->settings();
        $this->addValues($fields);

        $this->clearCacheIfNeed();

        // Group by positions / tabs
        $result = [];
        foreach ($fields as $field) {
            $side = $field->getRenderSettings()->getSide();
            $tab = $field->getRenderSettings()->getTab();

            if ($field instanceof MultilingualGroup) {
                if (!array_key_exists($tab->getKey(), array_get($result, $side, []))) {
                    $result[$side][$tab->getKey()] = ['tab' => $tab, 'fields' => []];
                }

                $result[$side][$tab->getKey()]['fields'][] = $field;
            } else {
                if (!array_key_exists($tab->getKey(), array_get($result, $side, []))) {
                    $result[$side][$tab->getKey()] = ['tab' => $tab, 'fields' => []];
                }

                $result[$side][$tab->getKey()]['fields'][] = $field;
            }
        }

        // Init JsValidator for all fields.
        list($rules, $attributes) = $this->parseRules($fields);
        $this->assets->addInlineScript(JsValidator::make($rules, [], $attributes, '#form'));

        $routeName = $this->routeName ? : preg_replace('/.settings$/', '', Route::currentRouteName());

        return view('cms-core::admin.settings.form', [
            'result' => $this->sortFieldsAndTabs($result),
            'routeName' => $routeName,
            'locales' => app('locales'),
            'action' => "{$routeName}.update-settings",
        ]);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $this->authorizeForAction('edit-settings');

        $fields = $this->settings();
        $this->addValues($fields);

        // Validate
        list($rules, $attributes) = $this->parseRules($fields);
        $rules = $this->convertFieldNamesToDots($rules);
        $attributes = $this->convertFieldNamesToDots($attributes);
        $this->validate($request, $rules, [], $attributes);

        $locales = app('locales');
        $module = $this->controllerBaseName();

        // Update all fields
        foreach ($fields as $field) {
            if ($field instanceof MultilingualGroup) {
                $renderSettings = $field->getRenderSettings();
                foreach ($field->getItems() as $item) {
                    $row = $this->initializeStorageFieldWithDefaultValue(
                        $item,
                        [
                            'module' => $module,
                            'group' => $renderSettings->getTab()->getKey(),
                            'key' => $item->getKey(),
                            'type' => $item->getType(),
                        ]
                    );

                    $item->setRenderSettings($renderSettings);
                    $row->updateValue($request, $item, $locales);
                }
            } else {
                $row = $this->initializeStorageFieldWithDefaultValue(
                    $field,
                    [
                        'module' => $module,
                        'group' => $field->getGroup(),
                        'key' => $field->getKey(),
                        'type' => $field->getType(),
                    ]
                );

                $row->updateValue($request, $field, $locales);
            }
        }

        $this->clearCache();

        flash(__('cms-core::admin.layout.Data successfully updated'), 'success');

        return $this->redirectAfterSave();
    }

    /**
     * @param $id
     * @param  string|null  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteSettingsFile($id, string $locale = null)
    {
        $this->authorizeForAction('edit-settings');

        /** @var Setting $row */
        $row = Setting::findOrFail($id);

        $row->deleteFile($locale);

        $this->clearCache();

        flash(__('cms-core::admin.layout.File successfully deleted'), 'success');

        return redirect()->back();
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     */
    abstract protected function settings(): array;

    /**
     * @return string
     */
    protected function controllerBaseName(): string
    {
        $controller = static::class;

        // Remove namespace
        $controller = substr($controller, strrpos($controller, '\\') + 1);

        // Remove "Controller" postfix
        $controller = str_replace('Controller', '', $controller);

        return kebab_case($controller);
    }

    /**
     * @param  array  $result
     * @return array
     */
    protected function sortFieldsAndTabs($result)
    {
        // Sort Tabs
        foreach ($result as $side => $tabs) {
            usort($result[$side], function ($tab1, $tab2) {
                return $tab1['tab']->getSort() <=> $tab2['tab']->getSort();
            });

            // Sort fields
            foreach ($result[$side] as $tabIndex => $tab) {
                usort($result[$side][$tabIndex]['fields'], function ($field1, $field2) {
                    return $field1->getSort() <=> $field2->getSort();
                });
            }
        }

        return $result;
    }

    /**
     * @param  AbstractField[]  $rows
     * @return array
     */
    protected function parseRules($rows)
    {
        $rules = [];
        $names = [];

        $locales = app('locales');
        foreach ($rows as $item) {
            if ($item instanceof MultilingualGroup) {
                foreach ($item->getItems() as $subItem) {
                    $subItem->setRenderSettings($item->getRenderSettings());
                    $this->parseItemRule($subItem, $rules, $names, $locales);
                }
            } else {
                $this->parseItemRule($item, $rules, $names, $locales);
            }
        }

        return [$rules, $names];
    }

    /**
     * @param  AbstractField  $item
     * @param $rules
     * @param $names
     * @param $locales
     */
    protected function parseItemRule(AbstractField $item, &$rules, &$names, $locales)
    {
        $itemRule = $item->getRules();
        if (!$itemRule) {
            return;
        }

        if ($item->isMultilingual()) {
            foreach ($locales as $locale => $language) {
                // Skip existing file
                if ($item->isAttachment() && $item->getValueObj()->fileExists($locale)) {
                    continue;
                }

                $key = $item->getRenderSettings()->getTab()->getKey() . '-' . $item->getInputName($locale);
                if ($item->getType() === AbstractField::TYPE_IMAGE) {
                    $key = "{$locale}[{$key}]";
                }
                $names[$key] = $item->getName() . ' (' . $language . ')';
                $rules[$key] = $item->getRules();
            }
        } else {
            // Skip existing file
            if ($item->isAttachment() && $item->getValueObj()->fileExists()) {
                return;
            }

            if ($itemRule = $item->getRules()) {
                $key = $item->getRenderSettings()->getTab()->getKey() . '-' . $item->getInputName();
                $names[$key] = $item->getName();
                $rules[$key] = $itemRule;
            }
        }
    }

    /**
     * @param  AbstractField[]  $fields
     */
    protected function addValues($fields)
    {
        $module = $this->controllerBaseName();
        $locales = app('locales');
        $fallbackLocale = app('translator')->getFallback();

        foreach ($fields as $field) {
            if ($field instanceof MultilingualGroup) {
                foreach ($field->getItems() as $item) {
                    $row = $this->initializeStorageFieldWithDefaultValue(
                        $item,
                        [
                            'module' => $module,
                            'group' => $field->getRenderSettings()->getTab()->getKey(),
                            'key' => $item->getKey(),
                            'type' => $item->getType(),
                        ]
                    );

                    if ($item->isMultilingual()) {
                        $values = [];
                        foreach ($locales as $locale => $language) {
                            $values[$locale] = $row->translateOrNew($locale)->value;
                        }
                        $item->setValue($values);
                    } else {
                        $item->setValue($row->translateOrNew($fallbackLocale)->value);
                    }

                    $item->setStorageId($row->id);
                    $item->setValueObj($row);
                }
            } else {
                $row = $this->initializeStorageFieldWithDefaultValue(
                    $field,
                    [
                        'module' => $module,
                        'group' => $field->getGroup(),
                        'key' => $field->getKey(),
                        'type' => $field->getType(),
                    ]
                );

                if ($field->isMultilingual()) {
                    $values = [];
                    foreach ($locales as $locale => $language) {
                        $values[$locale] = $row->translateOrNew($locale)->value;
                    }
                    $field->setValue($values);
                } else {
                    $field->setValue($row->translateOrNew($fallbackLocale)->value);
                }

                $field->setStorageId($row->id);
                $field->setValueObj($row);
            }
        }
    }

    /**
     * @param  AbstractField  $field
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|Setting|null
     */
    protected function initializeStorageFieldWithDefaultValue(AbstractField $field, array $columns)
    {
        $row = Setting::where($columns)->first();
        if (!$row) {
            Setting::where(array_except($columns, 'type'))->delete();

            $row = Setting::create($columns);
            foreach (app('locales') as $locale => $language) {
                $row->translateOrNew($locale)->value = $field->getDefault();
            }
            $row->save();

            $this->needClearCache = true;
        }

        if ($field->getType() === AbstractField::TYPE_IMAGE) {
            $row->image_settings = $field->extractSettings();
            $row->save();
        }

        return $row;
    }

    /**
     * @param  iterable  $rules
     * @return iterable
     */
    protected function convertFieldNamesToDots(iterable $rules): iterable
    {
        $result = [];
        foreach ($rules as $key => $rule) {
            $result[Helpers::convertFieldToDot($key)] = $rule;
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function indexRoute()
    {
        return route('admin.' . Helpers::currentController() . '.index');
    }

    /**
     * Clear cache after update settings.
     */
    protected function clearCache()
    {
        settings()->fresh();

        \Artisan::call('queue:restart'); // restart queue for update mail templates data, social links, etc.
    }

    /**
     * Clear cache if necessary.
     */
    protected function clearCacheIfNeed()
    {
        if ($this->needClearCache) {
            $this->clearCache();
        }
    }

    protected function setMeta()
    {
        if ($this instanceof SingleSettingsController) {
            $title = $this->title();
        } else {
            if (method_exists($this, 'getControllerTitle')) {
                $title = sprintf('%s: %s', $this->title(), __('cms-core::admin.settings.Settings'));
            } else {
                $title = __('cms-core::admin.settings.Settings');
            }
            $this->addBreadcrumb(__('cms-core::admin.settings.Settings'));
        }

        $this->pageName->setPageName($title);
    }
}
