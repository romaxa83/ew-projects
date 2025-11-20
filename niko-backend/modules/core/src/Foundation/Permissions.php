<?php

namespace WezomCms\Core\Foundation;

use Gate;
use Illuminate\Support\Collection;
use WezomCms\Core\Contracts\PermissionsContainerInterface;

class Permissions implements PermissionsContainerInterface
{
    /**
     * @var Collection
     */
    protected $items;

    /**
     * Permissions constructor.
     */
    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * @param  string  $key
     * @param  string|null  $name
     * @param  array  $gates
     * @return PermissionsContainerInterface
     */
    public function add(
        string $key,
        ?string $name,
        array $gates = ['view', 'create', 'edit', 'delete']
    ): PermissionsContainerInterface {
        $this->items->add(['type' => 'group', 'key' => $key, 'name' => $name, 'gates' => $gates]);

        $this->defineGates($key, $gates);

        return $this;
    }

    /**
     * Add one ability.
     *
     * @param  string  $ability
     * @param  string|null  $name
     * @param  callable|null  $callback
     * @return PermissionsContainerInterface
     */
    public function addItem(string $ability, ?string $name, callable $callback = null): PermissionsContainerInterface
    {
        $this->items->add(['type' => 'single', 'key' => $ability, 'name' => $name]);

        if (null === $callback) {
            $callback = $this->makeCallback($ability);
        }

        Gate::define($ability, $callback);

        return $this;
    }

    /**
     * @param  string  $ability
     * @param  string|null  $name
     * @return PermissionsContainerInterface
     */
    public function editSettings(string $ability, ?string $name): PermissionsContainerInterface
    {
        return $this->addItem($ability . '.edit-settings', $name);
    }

    /**
     * Add gate "show" to the last permission.
     *
     * @return PermissionsContainerInterface
     */
    public function withShow(): PermissionsContainerInterface
    {
        $last = $this->items->pop();

        if ($last['type'] === 'group') {
            $last['gates'][] = 'show';

            $this->sortGates($last);
        }

        $this->items->push($last);

        $ability = $last['key'] . '.show';

        Gate::define($ability, $this->makeCallback($ability));

        return $this;
    }
    /**
     * Add gate "edit-settings" to the last permission.
     *
     * @return PermissionsContainerInterface
     */
    public function withEditSettings(): PermissionsContainerInterface
    {
        $last = $this->items->pop();

        if ($last['type'] === 'group') {
            $last['gates'][] = 'edit-settings';

            $this->sortGates($last);
        }

        $this->items->push($last);

        $ability = $last['key'] . '.edit-settings';

        Gate::define($ability, $this->makeCallback($ability));

        return $this;
    }

    /**
     * Add gate "restore", "force-delete" to the last permission.
     *
     * @return PermissionsContainerInterface
     */
    public function withSoftDeletes(): PermissionsContainerInterface
    {
        $actions = ['restore', 'force-delete'];

        $last = $this->items->pop();

        if ($last['type'] === 'group') {
            $last['gates'] = array_unique(array_merge($last['gates'], $actions));

            $this->sortGates($last);
        }

        $this->items->push($last);

        foreach ($actions as $action) {
            $ability = $last['key'] . '.' . $action;

            Gate::define($ability, $this->makeCallback($ability));
        }

        return $this;
    }

    /**
     * @return iterable|Collection
     */
    public function getAll(): iterable
    {
        return $this->items;
    }

    /**
     * @param $key
     * @param  array  $gates
     */
    protected function defineGates($key, array $gates)
    {
        foreach ($gates as $action => $callback) {
            if (is_string($action)) {
                $ability = $key . '.' . $action;

                $default = is_callable($callback) ? $callback : $ability;

                Gate::define(
                    $ability,
                    $this->makeCallback(is_array($callback) ? array_get($callback, 'callback', $default) : $default)
                );
            } elseif (is_string($callback)) {
                $ability = $key . '.' . $callback;

                Gate::define($ability, $this->makeCallback($ability));
            }
        }
    }

    /**
     * @param $ability
     * @return \Closure
     */
    private function makeCallback($ability)
    {
        if (is_string($ability)) {
            return function ($user) use ($ability) {
                return $user->hasAccess($ability);
            };
        }

        return $ability;
    }

    /**
     * @param  array  $last
     */
    protected function sortGates(array &$last)
    {
        $last['gates'] = collect($last['gates'])->sortBy(function ($gate) {
            $position = array_search(
                $gate,
                ['view', 'show', 'create', 'edit', 'delete', 'restore', 'force-delete', 'edit-settings']
            );

            return $position !== false ? $position : 10;
        })->all();
    }
}
