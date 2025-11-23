<?php

namespace WezomCms\Core\Http\Controllers;

use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Throwable;
use View;
use WezomCms\Core\Contracts\Filter\RestoreFilterInterface;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Traits\ActionDeleteFileTrait;
use WezomCms\Core\Traits\ActionDeleteImageTrait;
use WezomCms\Core\Traits\Model\FileAttachable;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\RequestSetupTrait;

/**
 * @method array indexViewData($result, array $viewData): array
 * @method array formData($obj, array $viewData): array
 * @method array createViewData($obj, array $viewData): array
 * @method array editViewData($obj, array $viewData): array
 * @method array showViewData($obj, array $viewData): array
 *
 * @method afterSuccessfulSave($obj, FormRequest $request)
 *
 * @method afterSuccessfulStore($obj, FormRequest $request)
 * @method afterSuccessfulUpdate($obj, FormRequest $request)
 *
 * @method beforeDelete($obj, bool $force = false)
 * @method afterDelete($obj, bool $force = false)
 */
abstract class AbstractCRUDController extends AdminController
{
    use ActionDeleteImageTrait;
    use ActionDeleteFileTrait;
    use RequestSetupTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view;

    /**
     * Indicates whether to use pagination.
     *
     * @var bool
     */
    protected $paginate = true;

    /**
     * Index filter class name.
     *
     * @var string|null
     */
    protected $filter;

    /**
     * @var array
     */
    protected $imageRequestAssociation = [];

    /**
     * @var array
     */
    protected $fileRequestAssociation = [];

    protected bool $hideCreateBtn = false;

    /**
     * @param Request $request
     * @return Factory|\Illuminate\View\View|RedirectResponse
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorizeForAction('view', $this->model());

        $this->before();

        $this->indexButtons();

//        Button::make()
//            ->setName(__('cms-core::admin.buttons.Save'))
//            ->setAttribute('data-action', ButtonInterface::ACTION_SAVE)
//            ->setClass('btn-sm', 'btn-success', 'js-form-submit')
//            ->setIcon('fa-save')
//            ->setSortPosition(7);

        /** @var Builder|Model|LengthAwarePaginator $result */
        $result = $this->model()::query();

        $table = $result->getModel()->getTable();

        $result->select("{$table}.*");

        if (method_exists($result->getModel(), 'scopeFilter')) {
            $result = $result->filter($request->all());
        }
        $this->selectionIndexResult($result, $request);

        $result->orderByDesc($table . '.' . $result->getModel()->getKeyName());

        if ($this->paginate) {
            $result = $result->paginate($this->getLimit($request))->appends($request->query());

            // Redirect to previous page if result is empty & current page gt 1
            if ($result->isEmpty() && $result->currentPage() > 1) {
                $lastPage = (int)ceil($result->total() / $this->getLimit($request));

                if ($lastPage < 2) {
                    return redirect()->route($this->makeRouteName('index'));
                }

                return redirect()->route($this->makeRouteName('index'), [$result->getPageName() => $lastPage]);
            }
        } else {
            $result = $result->get();
        }

        $deleteText = null;
        if ($this->softDeleteEnabled()) {
            if (method_exists($this, 'buildTrashedTitle')) {
                $deleteText = __(
                    'cms-core::admin.layout.You can recover in the :section',
                    ['section' => $this->buildTrashedTitle()]
                );
            } else {
                $deleteText = __('cms-core::admin.layout.You can recover in the deleted section');
            }
        }

        $data = [
            'result' => $result,
            'viewPath' => $this->view,
            'routeName' => $this->routeName,
            'model' => $this->model(),
            'deleteText' => $deleteText,
            'perPageList' => $this->perPageList(),
        ];

        if ($filter = $this->filter()) {
            if (!is_object($filter)) {
                $filter = new $filter($this->model()::query());
            }

            if (($response = resolve(RestoreFilterInterface::class)->handle($request)) !== null) {
                return $response;
            }

            if (method_exists($filter, 'restoreSelectedOptions')) {
                $filter->restoreSelectedOptions($request);
            }

            $data['filterFields'] = $filter;
        }

        if (method_exists($this, 'indexViewData')) {
            $data = array_merge($data, $this->indexViewData($result, $data));
        }

        event('crud:index:' . $this->model(), ['result' => $result, 'data' => &$data]);

        return view($this->view . '.index', $data);
    }

    /**
     * Model name.
     *
     * @return string|Model
     */
    protected function model(): string
    {
        return $this->model;
    }

    protected function before()
    {
        $title = $this->title();
        $this->addBreadcrumb($title);
        $this->pageName->setPageName($title);
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    abstract protected function title(): string;

    /**
     * @param Builder $query
     * @param Request $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        //
    }

    /**
     * @param string $actionName
     * @return string
     */
    protected function makeRouteName(string $actionName): string
    {
        return $this->routeName . '.' . $actionName;
    }

    /**
     * @return bool
     */
    protected function softDeleteEnabled(): bool
    {
        return in_array('trashed', get_class_methods($this->model()));
    }

    /**
     * @return string|object|Filterable|null
     */
    protected function filter()
    {
        if ($this->filter !== null) {
            return $this->filter;
        }

        $modelName = $this->model();

        if (method_exists($modelName, 'getModelFilterClass')) {
            $filterClass = (new $modelName())->getModelFilterClass();

            if (class_exists($filterClass)) {
                return $filterClass;
            }
        }

        return null;
    }

    /**
     * @return Factory|\Illuminate\Contracts\View\View
     * @throws AuthorizationException
     * @throws BindingResolutionException
     */
    public function create()
    {
        $this->authorizeForAction('create', $this->model());

        $model = $this->model();

        $this->before();

        $this->formButtons('create', $model);

        $obj = new $model();

        $this->pageName->setPageName($this->title() . ': ' . __('cms-core::admin.layout.Creating'));
        $this->addBreadcrumb(__('cms-core::admin.layout.Create'));
        $this->renderJsValidator($this->createRequest());

        $data = [
            'obj' => $obj,
            'locales' => app('locales'),
            'viewPath' => $this->view,
            'routeName' => $this->routeName,
        ];

        if (method_exists($this, 'createViewData')) {
            $data = array_merge($data, $this->createViewData($obj, $data));
        } elseif (method_exists($this, 'formData')) {
            $data = array_merge($data, $this->formData($obj, $data));
        }

        event('crud:create-form:' . $this->model(), ['obj' => $obj, 'data' => &$data]);

        return View::first([$this->view . '.create', 'cms-core::admin.crud.create'], $data);
    }

    /**
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store()
    {
        $this->authorizeForAction('create', $this->model());

        $formRequest = app($this->createRequest()); // Resolve request && automatically validate

        try {
            $obj = DB::transaction(
                function () use ($formRequest) {
                    $model = $this->model();
                    /** @var Model $obj */
                    $obj = new $model();

                    $obj->fill($this->fillStoreData($obj, $formRequest));

                    event('crud:fill-store-data:' . $this->model(), compact('obj', 'formRequest'));

                    $obj->save();

                    if (method_exists($obj, 'imageSettings')) {
                        $this->uploadImages($obj, $formRequest);
                    }
                    if (method_exists($obj, 'fileSettings')) {
                        $this->uploadFiles($obj, $formRequest);
                    }

                    if (method_exists($this, 'afterSuccessfulStore')) {
                        $this->afterSuccessfulStore($obj, $formRequest);
                    } elseif (method_exists($this, 'afterSuccessfulSave')) {
                        call_user_func([$this, 'afterSuccessfulSave'], $obj, $formRequest);
                    }

                    event('crud:after-successful-store:' . $this->model(), compact('obj', 'formRequest'));

                    return $obj;
                }
            );

            flash(__('cms-core::admin.layout.Data successfully created'))->success();

            return $this->redirectAfterSave($obj, [$obj->id]);
        } catch (Throwable $e) {
            if (app()->isLocal()) {
                throw $e;
            }

            report($e);

            flash(__('cms-core::admin.layout.Error creating data'))->error();

            return redirect()->back()->withInput($formRequest->input());
        }
    }

    /**
     * @param Model $obj
     * @param FormRequest $request
     * @return mixed
     */
    protected function fillStoreData($obj, FormRequest $request): array
    {
        return $this->fill($obj, $request);
    }

    /**
     * @param Model $obj
     * @param FormRequest $request
     * @return array
     */
    protected function fill($obj, FormRequest $request): array
    {
        return $request->validated();
    }

    /**
     * @param Model|ImageAttachable $obj
     * @param FormRequest $formRequest
     */
    protected function uploadImages(Model $obj, FormRequest $formRequest)
    {
        foreach (array_keys($obj->imageSettings()) as $field) {
            $obj->uploadImage($this->associateSource('image', $obj, $field, $formRequest), $field);
        }
    }

    /**
     * @param string $type
     * @param Model|ImageAttachable|FileAttachable $obj
     * @param string $field
     * @param FormRequest $formRequest
     * @return array|UploadedFile|UploadedFile[]|null
     */
    protected function associateSource(string $type, Model $obj, string $field, FormRequest $formRequest)
    {
        $key = array_get($this->{$type . 'RequestAssociation'}, $field, $field);
        if (!$key) {
            return null;
        }

        if ($obj->{'is' . Str::ucfirst($type) . 'Multilingual'}($field)) {
            $result = [];

            foreach (array_keys(app('locales')) as $locale) {
                $result[$locale] = $formRequest->file("{$locale}.{$key}");
            }

            return $result;
        }

        return $formRequest->file($key);
    }

    /**
     * @param Model|FileAttachable $obj
     * @param FormRequest $formRequest
     */
    protected function uploadFiles(Model $obj, FormRequest $formRequest)
    {
        foreach (array_keys($obj->fileSettings()) as $field) {
            $obj->uploadFile($this->associateSource('file', $obj, $field, $formRequest), $field);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\View|RedirectResponse
     * @throws AuthorizationException
     * @throws BindingResolutionException
     */
    public function edit($id)
    {
        $this->before();

        $obj = $this->model()::find($id);

        $response = $this->redirectIfNoRecord($obj, $this->makeRouteName('index'), $this->abilityPrefix() . '.view');
        if ($response) {
            return $response;
        }

        $this->authorizeForAction('edit', $obj);

        $this->formButtons('edit', $obj);

        $this->pageName->setPageName($this->title() . ': ' . __('cms-core::admin.layout.Editing'));
        $this->addBreadcrumb(__('cms-core::admin.layout.Edit'));
        $this->renderJsValidator($this->updateRequest());

        $data = [
            'obj' => $obj,
            'locales' => app('locales'),
            'viewPath' => $this->view,
            'routeName' => $this->routeName,
            'hideCreateBtn' => $this->hideCreateBtn
        ];

        if (method_exists($this, 'editViewData')) {
            $data = array_merge($data, $this->editViewData($obj, $data));
        } elseif (method_exists($this, 'formData')) {
            $data = array_merge($data, $this->formData($obj, $data));
        }

        event('crud:edit-form:' . $this->model(), ['obj' => $obj, 'data' => &$data]);

        return View::first([$this->view . '.edit', 'cms-core::admin.crud.edit'], $data);
    }

    /**
     * @return null|string
     */
    protected function abilityPrefix(): ?string
    {
        return app(PermissionsContainerInterface::class)->getModelAbility(new $this->model());
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        /** @var Model $obj */
        $obj = $this->model()::findOrFail($id);

        $this->authorizeForAction('edit', $obj);

        /** @var FormRequest $formRequest */
        $formRequest = app($this->updateRequest()); // Resolve request && automatically validate

        try {
            DB::transaction(
                function () use ($obj, $formRequest) {
                    $data = $this->fillUpdateData($obj, $formRequest);

                    event('crud:fill-update-data:' . $this->model(), compact('obj', 'formRequest'));

                    $obj->update($data);

                    if (method_exists($obj, 'imageSettings')) {
                        $this->uploadImages($obj, $formRequest);
                    }
                    if (method_exists($obj, 'fileSettings')) {
                        $this->uploadFiles($obj, $formRequest);
                    }

                    if (method_exists($this, 'afterSuccessfulUpdate')) {
                        $this->afterSuccessfulUpdate($obj, $formRequest);
                    } elseif (method_exists($this, 'afterSuccessfulSave')) {
                        $this->afterSuccessfulSave($obj, $formRequest);
                    }

                    event('crud:after-successful-update:' . $this->model(), compact('obj', 'formRequest'));
                }
            );

            flash(__('cms-core::admin.layout.Data successfully updated'))->success();

            return $this->redirectAfterSave($obj);
        } catch (Throwable $e) {
            flash($e->getMessage())->error();

            return redirect()->back()->withInput($formRequest->input());
        }
    }

    /**
     * @param Model $obj
     * @param FormRequest $request
     * @return mixed
     */
    protected function fillUpdateData($obj, FormRequest $request): array
    {
        return $this->fill($obj, $request);
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy($id)
    {
        $obj = $this->model()::findOrFail($id);

        $this->authorizeForAction('delete', $obj);

        if (method_exists($this, 'beforeDelete')) {
            $result = $this->beforeDelete($obj);
            if (null !== $result) {
                return $result;
            }
        }

        if ($obj->delete()) {
            if (method_exists($this, 'afterDelete')) {
                $this->afterDelete($obj);
            }
            flash(__('cms-core::admin.layout.Data deleted successfully'))->success();
        } else {
            flash(__('cms-core::admin.layout.Data deletion error'))->error();
        }

        return redirect()->route($this->makeRouteName('index'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function massDestroy(Request $request)
    {
        $ids = $request->get('IDS', []);
        if (empty($ids)) {
            return $this->error(__('cms-core::admin.layout.Please select at least one entry to delete'));
        }

        $forceDelete = (bool)$request->get('force_delete', false);

        /** @var Collection|Model[] $result */
        $result = $this->model()::whereKey($ids)
            ->when(
                $forceDelete,
                function ($query) {
                    $query->onlyTrashed();
                }
            )
            ->get()
            ->filter(
                function (Model $item) use ($forceDelete) {
                    return $this->allowsForAction($forceDelete ? 'force-delete' : 'delete', $item);
                }
            );

        if ($result->isEmpty()) {
            flash()->error(__('cms-core::admin.layout.Deleting selected entries is prohibited'));
            return $this->error(['reload' => true]);
        }

        if (method_exists($this, 'beforeDelete')) {
            $result = $result->filter(
                function ($item) use ($forceDelete) {
                    return $this->beforeDelete($item, $forceDelete) === null;
                }
            );
        }

        if ($result->isEmpty()) {
            return $this->error(['reload' => true]);
        }

        foreach ($result as $item) {
            if ($forceDelete) {
                $item->forceDelete();
            } else {
                $item->delete();
            }

            if (method_exists($this, 'afterDelete')) {
                $this->afterDelete($item, $forceDelete);
            }
        }

        flash()->success(__('cms-core::admin.layout.Data deleted successfully'));
        return $this->success(['reload' => true]);
    }
}
