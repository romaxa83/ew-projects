<?php

namespace WezomCms\Cars\Http\Controllers\Admin;

use WezomCms\Cars\Http\Requests\Admin\EngineTypeRequest;
use WezomCms\Cars\Models\EngineType;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;

class EngineTypeController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = EngineType::class;

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
    protected $view = 'cms-cars::admin.engine-types';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.car-engine-types';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = EngineTypeRequest::class;

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
        return __('cms-cars::admin.Engine types');
    }
}




