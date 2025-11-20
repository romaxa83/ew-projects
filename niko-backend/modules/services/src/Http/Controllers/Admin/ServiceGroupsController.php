<?php

namespace WezomCms\Services\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Services\Http\Requests\Admin\ServiceGroupRequest;
use WezomCms\Services\Models\ServiceGroup;

class ServiceGroupsController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = ServiceGroup::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-services::admin.groups';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.service-groups';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = ServiceGroupRequest::class;

    protected $hideCreateBnt = true;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-services::admin.Service groups');
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->orderBy('sort');
    }
}
