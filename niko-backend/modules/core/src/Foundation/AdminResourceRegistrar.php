<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Routing\ResourceRegistrar;

class AdminResourceRegistrar extends ResourceRegistrar
{
    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = [
        'index',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
        'massDestroy',
        'deleteFile',
        'deleteImage'
    ];

    /**
     * Add the mass destroy method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceMassDestroy($name, $base, $controller, $options)
    {
        return $this->router->post(
            $this->getResourceUri($name) . '/mass-delete',
            ['as' => $name . '.mass-delete', 'uses' => $controller . '@massDestroy']
        );
    }

    /**
     * Add the mass destroy method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceSettings($name, $base, $controller, $options)
    {
        // Show form
        $uri = $this->getResourceUri($name);
        $this->router->get($uri . '/settings/form', ['as' => $name . '.settings', 'uses' => $controller . '@settingsForm']);

        // Update settings
        $this->router->post(
            $uri . '/settings',
            ['as' => $name . '.update-settings', 'uses' => $controller . '@updateSettings']
        );

        // Delete file
        return $this->router->get(
            $uri . '/delete-settings-file/{id}/{locale?}',
            ['as' => $name . '.delete-settings-file', 'uses' => $controller . '@deleteSettingsFile']
        )->where('id', '\d+');
    }

    /**
     * Add the delete image method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceDeleteImage($name, $base, $controller, $options)
    {
        return $this->router->get(
            $this->getResourceUri($name) . '/delete-image/{id?}/{field?}/{locale?}',
            ['as' => $name . '.delete-image', 'uses' => $controller . '@deleteImage']
        )->where('id', '\d+');
    }

    /**
     * Add the delete file method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceDeleteFile($name, $base, $controller, $options)
    {
        return $this->router->get(
            $this->getResourceUri($name) . '/delete-file/{id?}/{field?}/{locale?}',
            ['as' => $name . '.delete-file', 'uses' => $controller . '@deleteFile']
        )->where('id', '\d+');
    }


    /**
     * Add the trashed method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceTrashed($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);
        $resourceUri = $this->getResourceUri($name);

        data_set($options, 'names.deleteAllTrashed', $name . '.delete-all-trashed');
        data_set($options, 'names.massRestore', $name . '.mass-restore');


        // Delete all trashed
        $this->router->get(
            $resourceUri . '/delete-all-trashed',
            $this->getResourceAction($name, $controller, 'deleteAllTrashed', $options)
        );

        // Mass restore
        $this->router->post(
            $resourceUri . '/mass-restore',
            $this->getResourceAction($name, $controller, 'massRestore', $options)
        );

        // Trashed list
        return $this->router->get(
            $resourceUri . '/trashed',
            $this->getResourceAction($name, $controller, 'trashed', $options)
        );
    }

    /**
     * Add the restore method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceRestore($name, $base, $controller, $options)
    {
        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name) . '/{' . $base . '}/restore';

        $action = $this->getResourceAction($name, $controller, 'restore', $options);

        return $this->router->patch($uri, $action);
    }

    /**
     * Add the force destroy method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceForceDestroy($name, $base, $controller, $options)
    {
        data_set($options, 'names.forceDestroy', $name . '.force-destroy');

        $name = $this->getShallowName($name, $options);

        $uri = $this->getResourceUri($name) . '/{' . $base . '}/force-destroy';

        $action = $this->getResourceAction($name, $controller, 'forceDestroy', $options);

        return $this->router->delete($uri, $action);
    }

    /**
     * Get the applicable resource methods.
     *
     * @param  array  $defaults
     * @param  array  $options
     * @return array
     */
    protected function getResourceMethods($defaults, $options)
    {
        $methods = $defaults;

        if (isset($options['with'])) {
            $methods = array_merge($methods, (array) $options['with']);
        }

        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array) $options['only']);
        }

        if (isset($options['except'])) {
            $methods = array_diff($methods, (array) $options['except']);
        }

        // sort methods
        $methods = collect($methods)->sortBy(function ($method) {
            $position = array_search(
                $method,
                [
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                    'deleteFile',
                    'deleteImage',
                    'massDestroy',
                    'forceDestroy',
                    'trashed',
                    'restore',
                    'settings',
                    'show'
                ]
            );

            return $position !== false ? $position : 20;
        })->all();

        return $methods;
    }
}
