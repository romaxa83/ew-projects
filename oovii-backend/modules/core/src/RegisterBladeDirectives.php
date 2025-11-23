<?php

namespace WezomCms\Core;

use Blade;
use Str;

class RegisterBladeDirectives
{
    public static function globalDirectives()
    {
        Blade::directive('widget', function ($arguments) {
            return "<?php echo app('widget')->show($arguments); ?>";
        });

        Blade::directive('money', function ($arguments) {
            return '<?php echo money(' . $arguments . '); ?>';
        });
    }

    public static function adminDirectives()
    {
        Blade::directive('statuses', function ($expression) {
            return "<?php echo app('view')->make('cms-core::admin.directives.statuses', is_array($expression) ? $expression : ['obj' => $expression]); ?>";
        });

        Blade::directive('smallStatus', function ($expression) {
            return "<?php echo app('view')->make('cms-core::admin.directives.small-status', is_array($expression) ? $expression : ['obj' => $expression]); ?>";
        });

        Blade::directive('gotosite', function ($arguments) {
            return "<?php echo app('view')->make('cms-core::admin.directives.gotosite', ['obj' => $arguments]); ?>";
        });

        // Mass delete
        Blade::directive('massControl', function ($expression) {
            if (Str::contains($expression, ['[', ']', 'compact'])) {
                return "<?php echo app('view')->make('cms-core::admin.directives.mass-delete.control', $expression); ?>";
            }

            $parts = explode(',', $expression);
            if (count($parts) === 2) {
                return "<?php echo app('view')->make('cms-core::admin.directives.mass-delete.control', ['routeName' => {$parts[0]}, 'forceDelete' => {$parts[1]}]); ?>";
            } else {
                return "<?php echo app('view')->make('cms-core::admin.directives.mass-delete.control', ['routeName' => $expression]); ?>";
            }
        });

        Blade::directive('massCheck', function ($obj) {
            return "<?php echo app('view')->make('cms-core::admin.directives.mass-delete.check', ['obj' => $obj]); ?>";
        });

        Blade::directive('showResource', function ($expression) {
            // If passed array
            if (Str::contains($expression, ['[', ']'])) {
                return "<?php echo app('view')->make('cms-core::admin.directives.resource.show', $expression); ?>";
            }

            $parts = explode(',', $expression);
            if (count($parts) === 2) {
                return "<?php echo app('view')->make('cms-core::admin.directives.resource.show', ['obj' => {$parts[0]}, 'text' => {$parts[1]}]); ?>";
            }

            return "<?php echo app('view')->make('cms-core::admin.directives.resource.show', ['obj' => $expression]); ?>";
        });

        Blade::directive('editResource', function ($expression) {
            // If passed array
            if (Str::contains($expression, ['[', ']'])) {
                return "<?php echo app('view')->make('cms-core::admin.directives.resource.edit', $expression); ?>";
            }

            $parts = explode(',', $expression);
            if (count($parts) === 2) {
                return "<?php echo app('view')->make('cms-core::admin.directives.resource.edit', ['obj' => {$parts[0]}, 'text' => {$parts[1]}]); ?>";
            }

            return "<?php echo app('view')->make('cms-core::admin.directives.resource.edit', ['obj' => $expression]); ?>";
        });

        Blade::directive('deleteResource', function ($expression) {
            return "<?php echo app('view')->make('cms-core::admin.directives.resource.delete', is_array($expression) ? $expression : ['obj' => $expression]); ?>";
        });

        Blade::directive('restoreResource', function ($expression) {
            return "<?php echo app('view')->make('cms-core::admin.directives.resource.restore', is_array($expression) ? $expression : ['obj' => $expression]); ?>";
        });

        Blade::directive('langTabs', function () {
            $randId = str_random();

            $initLoop = "\$__currentLoopData = app('locales'); \$__env->addLoop(\$__currentLoopData);";

            $iterateLoop = '$__env->incrementLoopIndices(); $loop = $__env->getLastLoop();';

            return '<ul class="nav nav-tabs customtab js-lang-tabs js-copy-lang-fields" role="tablist" data-locales="<?= e(json_encode(array_keys(app(\'locales\')))) ?>" <?php if(count(app(\'locales\')) < 2): ?> hidden <?php endif; ?>>
                        <?php ' . $initLoop . ' foreach($__currentLoopData as $locale => $language): ' . $iterateLoop . ' ?>
                           <li class="nav-item">
                               <a href="#form-lang-tab-' . $randId . '-<?= $locale ?>"
                                  class="nav-link py-1 <?= $loop->first ? \'active\' : \'\' ?>" role="tab"
                                  data-toggle="tab"><?= e($language) ?></a>
                           </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <div class="tab-content">
                        <?php ' . $initLoop . ' foreach($__currentLoopData as $locale => $language): ' . $iterateLoop . ' ?>
                             <div class="tab-pane p-t-10 p-b-10 <?= $loop->first ? \'active\' : \'\' ?>"
                                   id="form-lang-tab-' . $randId . '-<?= e($locale) ?>" data-locale="<?= e($locale) ?>" role="tabpanel">';
        });

        Blade::directive('endLangTabs', function () {
            return '</div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>';
        });

        Blade::directive('tabs', function ($expression) {
            $randId = str_random();

            return '<ul class="nav nav-tabs customtab" role="tablist">
                        <?php foreach(array_keys(' . $expression . ') as $index => $tabName): ?>
                             <li class="nav-item">
                                 <a href="#form-lang-tab-' . $randId . '-<?= $index ?>"
                                     class="nav-link py-1 <?= $index === 0 ? \'active\' : \'\' ?>"
                                     role="tab"
                                     data-toggle="tab"><?= e($tabName) ?></a>
                             </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="tab-content">
                        <?php foreach(array_values(' . $expression . ') as $index => $view): ?>
                            <div class="tab-pane p-t-10 p-b-10 <?= $index === 0 ? \'active\' : \'\' ?>"
                                 id="form-lang-tab-' . $randId . '-<?= $index ?>" role="tabpanel">
                                 <?= $__env->make($view, \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>';
        });
    }
}
