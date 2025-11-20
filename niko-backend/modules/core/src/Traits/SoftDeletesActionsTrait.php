<?php

namespace WezomCms\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Contracts\Filter\RestoreFilterInterface;
use WezomCms\Core\Foundation\Buttons\Link;
use WezomCms\Core\Traits\Model\Filterable;

/**
 * Trait SoftDeletesActionsTrait
 * @method array trashedViewData($result, array $viewData): array
 */
trait SoftDeletesActionsTrait
{
    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionTrashedResult($query, Request $request)
    {
    }

    /**
     * @return string|object|Filterable|null
     */
    protected function trashedFilter()
    {
        return $this->filter();
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function trashed(Request $request)
    {
        $this->authorizeForAction('view', $this->model());

        $this->before();

        $title = $this->buildTrashedTitle();
        $this->addBreadcrumb($title);
        $this->pageName->setPageName($title);


        /** @var Builder|Model|LengthAwarePaginator $query */
        $query = $this->model()::query()->onlyTrashed();

        if (method_exists($query->getModel(), 'scopeFilter')) {
            $query = $query->filter($request->all());
        }

        $this->selectionTrashedResult($query, $request);

        $query->orderByDesc($query->getModel()->getKeyName());

        if (property_exists($this, 'paginate')) {
            $result = $query->paginate($this->getLimit($request))->appends($request->query());

            // Redirect to previous page if result is empty & current page gt 1
            if ($result->isEmpty() && $result->currentPage() > 1) {
                $lastPage = (int)ceil($result->total() / $this->getLimit($request));

                if ($lastPage < 2) {
                    return redirect()->route($this->makeRouteName('trashed'));
                }

                return redirect()->route($this->makeRouteName('trashed'), [$result->getPageName() => $lastPage]);
            }
        } else {
            $result = $query->get();
        }

        $this->trashedButtons($result);

        $data = [
            'result' => $result,
            'viewPath' => $this->view,
            'routeName' => $this->routeName,
            'model' => $this->model(),
            'perPageList' => $this->perPageList(),
        ];

        if ($filter = $this->trashedFilter()) {
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

        if (method_exists($this, 'trashedViewData')) {
            $data = array_merge($data, $this->trashedViewData($result, $data));
        }

        event('crud:trashed:' . $this->model(), compact('result', 'data'));

        return view($this->view . '.trashed', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $obj = $this->model()::onlyTrashed()->findOrFail($id);

        $this->authorizeForAction('restore', $obj);

        if ($obj->restore()) {
            flash(__('cms-core::admin.layout.Record successfully restored'), 'success');
        } else {
            flash(__('cms-core::admin.layout.Record restore error'), 'warning');
        }

        return redirect()->back();
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function massRestore(Request $request)
    {
        $ids = $request->get('IDS', []);
        if (empty($ids)) {
            return $this->error(__('cms-core::admin.layout.Please select at least one entry to restore'));
        }

        $this->model()::onlyTrashed()
            ->whereKey($ids)
            ->each(function ($obj) {
                if ($this->allowsForAction('restore', $obj)) {
                    $obj->restore();
                }
            });

        return $this->success(['reload' => true]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDestroy($id)
    {
        $obj = $this->model()::onlyTrashed()->findOrFail($id);

        $this->authorizeForAction('force-delete', $obj);

        if (method_exists($this, 'beforeDelete')) {
            if (($result = $this->beforeDelete($obj, true)) !== null) {
                return $result;
            }
        }

        if ($obj->forceDelete()) {
            flash(__('cms-core::admin.layout.Data deleted successfully'))->success();
        } else {
            flash(__('cms-core::admin.layout.Data deletion error'))->error();
        }

        return redirect()->back();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAllTrashed()
    {
        $this->model()::onlyTrashed()
            ->each(function (Model $obj) {
                if ($this->allowsForAction('force-delete', $obj)) {
                    $obj->forceDelete();
                }
            }, 100);

        flash(__('cms-core::admin.layout.Data deleted successfully'))->success();

        return redirect()->route($this->makeRouteName('trashed'));
    }

    /**
     * Register trashed list buttons.
     * @param  Collection|LengthAwarePaginator  $result
     */
    protected function trashedButtons($result)
    {
        $buttonsContainer = app(ButtonsContainerInterface::class);

        $buttonsContainer->add(Link::make()
            ->setName(__('cms-core::admin.layout.Back'))
            ->setLink(route($this->makeRouteName('index')))
            ->setClass('btn-sm btn-primary')
            ->setIcon('fa-long-arrow-left')
            ->setSortPosition(10));

        if ($result->isNotEmpty()) {
            $buttonsContainer->add(Link::make()
                ->setName(__('cms-core::admin.layout.Delete all trashed'))
                ->setLink(route($this->makeRouteName('delete-all-trashed')))
                ->setClass('btn-sm btn-danger')
                ->setIcon('fa-trash')
                ->setAttribute('onclick', 'return confirmDelete(this)')
                ->setSortPosition(11));
        }
    }

    /**
     * @return string
     */
    protected function buildTrashedTitle(): string
    {
        return __('cms-core::admin.layout.Trashed :title', ['title' => mb_strtolower($this->title())]);
    }
}
