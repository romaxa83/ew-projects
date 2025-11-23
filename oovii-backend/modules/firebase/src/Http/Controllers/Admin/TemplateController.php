<?php

namespace WezomCms\Firebase\Http\Controllers\Admin;

use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Models\Administrator;
use WezomCms\Firebase\Http\Requests\Admin\TemplateRequest;
use WezomCms\Firebase\Models\Template;
use WezomCms\Imports\Http\Requests\Admin\ImportRequest;
use WezomCms\Imports\Jobs\ProductImportJob;
use WezomCms\Imports\Models\Import;
use WezomCms\Imports\Repositories\ImportRepository;
use WezomCms\Imports\Services\ImportService;

class TemplateController extends AbstractCRUDController
{
    protected $model = Template::class;

    protected $view = 'cms-firebase::admin.templates';

    protected $routeName = 'admin.fcm-templates';

    protected $updateRequest = TemplateRequest::class;

    protected bool $hideCreateBtn = true;

    public function __construct()
    {
        parent::__construct();
    }

    protected function title(): string
    {
        return __('cms-firebase::admin.template.many');
    }
}


