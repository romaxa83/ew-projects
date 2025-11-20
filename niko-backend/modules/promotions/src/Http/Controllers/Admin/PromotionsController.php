<?php

namespace WezomCms\Promotions\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Promotions\Http\Requests\Admin\PromotionsRequest;
use WezomCms\Promotions\Models\Promotions;

class PromotionsController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Promotions::class;

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
    protected $view = 'cms-promotions::admin.promotions';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.promotions';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = PromotionsRequest::class;

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
        return __('cms-promotions::admin.Promotions');
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


