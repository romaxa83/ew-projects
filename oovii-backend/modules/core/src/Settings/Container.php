<?php

namespace WezomCms\Core\Settings;

use Arr;
use Cache;
use Illuminate\Contracts\Foundation\Application;
use WezomCms\Core\Contracts\SettingsInterface;
use WezomCms\Core\Models\Setting;
use WezomCms\Core\Settings\Fields\AbstractField;

class Container implements SettingsInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Application
     */
    protected $app;

    /**
     * Settings constructor
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->load();
    }

    /**
     * @param  string  $name
     * @param  mixed|null  $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return Arr::get($this->data, $name, $default);
    }

    /**
     * @param  string  $name
     * @param  mixed  $value
     * @return SettingsInterface
     */
    public function set(string $name, $value): SettingsInterface
    {
        Arr::set($this->data, $name, $value);

        return $this;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @param  string  $name
     * @return SettingsInterface $this
     */
    public function forget(string $name): SettingsInterface
    {
        Arr::forget($this->data, $name);

        return $this;
    }

    /**
     * Fresh all cached data
     */
    public function fresh()
    {
        foreach ($this->app['locales'] as $locale => $language) {
            Cache::forget("settings.{$locale}");
        }

        $this->data = [];

        $this->load();
    }

    protected function load()
    {
        try {
            if ($this->app->runningUnitTests() || $this->app->runningInConsole() || $this->app['isBackend']) {
                $rows = Setting::withTranslation()->get();
            } else {
                $rows = Cache::rememberForever('settings.' . $this->app->getLocale(), function () {
                    return Setting::withTranslation()->get();
                });
            }

            foreach ($rows as $row) {
                array_set(
                    $this->data,
                    implode('.', [$row->module, $row->group, $row->key]),
                    in_array($row->type, [AbstractField::TYPE_IMAGE, AbstractField::TYPE_FILE]) ? $row : $row->value
                );
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
