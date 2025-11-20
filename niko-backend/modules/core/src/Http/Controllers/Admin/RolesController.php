<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Illuminate\Support\Collection;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Http\Requests\Admin\RoleRequest;
use WezomCms\Core\Models\Role;

class RolesController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-core::admin.auth.roles';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.roles';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = RoleRequest::class;


    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-core::admin.roles.Roles');
    }

    /**
     * @param  Role  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function formData($obj, array $viewData): array
    {
        return [
            'permissions' => $this->getPermissionsTree(),
            'selectedPermissions' => (array) (old('permissions') ?: $obj->permissions),
        ];
    }

    /**
     * @return Collection
     */
    private function getPermissionsTree()
    {
        /** @var PermissionsContainerInterface $container */
        $container = app(PermissionsContainerInterface::class);

        // Default translations
        $translations = [
            'view' => __('cms-core::admin.roles.view'),
            'show' => __('cms-core::admin.roles.show'),
            'create' => __('cms-core::admin.roles.create'),
            'edit' => __('cms-core::admin.roles.edit'),
            'delete' => __('cms-core::admin.roles.delete'),
            'force-delete' => __('cms-core::admin.roles.force-delete'),
            'restore' => __('cms-core::admin.roles.restore'),
            'edit-settings' => __('cms-core::admin.roles.edit-settings'),
        ];

        $groups = [];
        foreach ($container->getAll() as $item) {
            switch ($item['type']) {
                case 'single':
                    $name = __($item['name']);

                    $parts = explode('.', $item['key'], 2);
                    if (count($parts) == 1) {
                        $groups[$parts[0]] = ['name' => $name];
                    } else {
                        $key = $parts[0];

                        if (isset($groups[$key])) {
                            $groups[$key]['checkboxes'][$item['key']] = $name;
                        } else {
                            $groups[$key] = [
                                'name' => $name,
                                'checkboxes' => [$item['key'] => $name],
                            ];
                        }
                    }
                    break;
                case 'group':
                    $key = $item['key'];

                    $checkboxes = [];
                    foreach ($item['gates'] as $action => $name) {
                        if (is_string($action)) {
                            $default = array_get($translations, $action);

                            $ability = $key . '.' . $action;

                            if (is_array($name)) {
                                $checkboxes[$ability] = __(array_get($name, 'name', $default));
                            } elseif (is_string($name)) {
                                $checkboxes[$ability] = __($name ?: $default);
                            } else {
                                $checkboxes[$ability] = $default;
                            }
                        } elseif (is_string($name)) {
                            $checkboxes[$key . '.' . $name] = __(array_get($translations, $name, $name));
                        }
                    }

                    if (isset($groups[$key])) {
                        $groups[$key]['name'] = __($item['name']);
                        $groups[$key]['checkboxes'] = array_merge($groups[$key]['checkboxes'], $checkboxes);
                    } else {
                        $groups[$key] = [
                            'name' => __($item['name']),
                            'checkboxes' => $checkboxes,
                        ];
                    }
                    break;
            }
        }

        unset($groups['administrators'], $groups['roles']);

        return collect($groups)->sortBy('name');
    }
}
