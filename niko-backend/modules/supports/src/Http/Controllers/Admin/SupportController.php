<?php

namespace WezomCms\Supports\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Promotions\Http\Requests\Admin\PromotionsRequest;
use WezomCms\Promotions\Models\Promotions;
use WezomCms\Supports\Http\Requests\Admin\SupportRequest;
use WezomCms\Supports\Models\Support;

class SupportController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Support::class;

    /**
     * Indicates whether to use pagination.
     *
     * @var bool
     */
    protected $paginate = false;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-supports::admin.supports';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.supports';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = SupportRequest::class;

    protected $hideCreateBnt = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-support::admin.Support');
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->orderBy('id', 'desc');
    }
}


