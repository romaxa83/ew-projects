<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Cache;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use WezomCms\Core\Enums\TranslationSide;
use WezomCms\Core\Foundation\DatabaseTranslationStorage;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Models\Translation;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;

class TranslationsController extends AdminController
{
    use AjaxResponseStatusTrait;

    protected $viewPath = 'cms-core::admin.translations';

    protected $routeName = 'admin.translations';

    /**
     * @return null|string
     */
    protected function abilityPrefix(): ?string
    {
        return 'translations';
    }

    /**
     * @param  string  $side
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws AuthorizationException
     */
    public function index(string $side)
    {
        $adminLocales = config('cms.core.translations.admin.locales', []);

        if (!TranslationSide::hasValue($side) || $side === TranslationSide::ADMIN && count($adminLocales) < 2) {
            abort(404);
        }

        $this->authorizeForAction('view', Translation::class);

        if (count($adminLocales) > 1) {
            $title = sprintf(
                '%s (%s)',
                __('cms-core::admin.translations.Translations'),
                TranslationSide::getDescription($side)
            );
        } else {
            $title = __('cms-core::admin.translations.Translations');
        }

        $this->addBreadcrumb($title);
        $this->pageName->setPageName($title);

        if ($side === TranslationSide::ADMIN) {
            $locales = collect($adminLocales)
                ->map(function ($el) {
                    return array_get($el, 'name');
                })
                ->all();
        } else {
            $locales = app('locales');
        }

        $this->addEditableDataTable($locales, $side);

        $data = [
            'translations' => Translation::getTranslations($side, array_keys($locales)),
            'locales' => $locales,
            'viewPath' => $this->viewPath,
            'routeName' => $this->routeName,
        ];

        return view($this->viewPath . '.index', $data);
    }

    /**
     * @param  string  $side
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(string $side, Request $request)
    {
        $adminLocales = config('cms.core.translator.admin.locales', []);

        if (!TranslationSide::hasValue($side) || $side === TranslationSide::ADMIN && count($adminLocales) < 2) {
            abort(404);
        }

        if (!$this->allowsForAction('edit', Translation::class)) {
            return $this->error(__('cms-core::admin.auth.You do not have access to this section'));
        }

        $translation = Translation::find($request->get('id'));

        if (!$translation) {
            return $this->error(__('cms-core::admin.layout.Error updating data'));
        }

        $translation->update($request->only('text'));

        Cache::forget(DatabaseTranslationStorage::CACHE_KEY);

        return $this->success(__('cms-core::admin.layout.Data successfully updated'));
    }

    /**
     * @param  array  $locales
     * @param  string  $side
     */
    private function addEditableDataTable(array $locales, string $side): void
    {
        $options = json_encode([
            'language' => [
                'url' => url('vendor/cms/core/plugins/datatables/i18n/' . app()->getLocale() . '.json'),
            ],
            'lengthChange' => false,
            'info' => false,
        ]);

        $columns = json_encode(range(1, count($locales)));

        $script = <<<JS
$(document).ready(function () {
    var table = $('#dataTable').DataTable({$options});

    table.MakeCellsEditable({
        "onUpdate": onUpdateCallback,
        "columns": {$columns},
        "inputCss": 'form-control'
    });
    
    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function onUpdateCallback (updatedCell, updatedRow, oldValue) {
        var id = $(updatedCell.node()).data('id');
        var text = updatedCell.data();
        var escapedText = htmlEntities(text);

        updatedCell.data(escapedText);
        if (escapedText !== oldValue) {
            axios.post(route('admin.translations.update', '{$side}'), {id: id, text: text});
        }
    }
});
JS;

        $this->assets->addInlineScript($script);
    }
}
