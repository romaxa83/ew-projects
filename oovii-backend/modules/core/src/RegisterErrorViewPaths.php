<?php

namespace WezomCms\Core;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\View;

class RegisterErrorViewPaths
{
    /**
     * Register the error view paths.
     *
     * @return void
     */
    public function __invoke()
    {
        $paths = collect(config('view.paths'));

        $hints = app('view')->getFinder()->getHints();

        // Add ui path if present
        if ($uiPath = array_get($hints, 'cms-ui.0')) {
            $paths->prepend($uiPath);
        }

        // Add core backend path
        if (app('isBackend') && $corePath = array_get($hints, 'cms-core.0')) {
            $paths->prepend($corePath . '/admin');
        }

        $laravelExceptionViews = dirname((new \ReflectionClass(Handler::class))
                ->getFileName()) . '/views';

        View::replaceNamespace('errors', $paths->map(function ($path) {
            return "{$path}/errors";
        })->push($laravelExceptionViews)->all());
    }
}
