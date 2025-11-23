<?php

namespace WezomCms\Imports\Http\Controllers\Admin;

use Auth;
use DB;
use Illuminate\Http\Request;
use Throwable;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Models\Administrator;
use WezomCms\Imports\Http\Requests\Admin\ImportRequest;
use WezomCms\Imports\Jobs\ProductImportJob;
use WezomCms\Imports\Models\Import;
use WezomCms\Imports\Repositories\ImportRepository;
use WezomCms\Imports\Services\ImportService;

class ImportController extends AbstractCRUDController
{
    protected $model = Import::class;

    protected $view = 'cms-imports::admin.imports';

    protected $routeName = 'admin.imports';

    protected $createRequest = ImportRequest::class;

    public function __construct(
        protected ImportService $service,
        protected ImportRepository $repo,
    )
    {
        parent::__construct();
    }

    protected function title(): string
    {
        return __('cms-imports::admin.imports');
    }

    protected function selectionIndexResult($query, Request $request)
    {
        $query->with('administrator');
    }

    public function store()
    {
        /** @var $user Administrator */
        $user = Auth::user();
        $this->authorizeForAction('create', $this->model());

//        $lastImport = $this->repo->getLastRow();
//        if ($lastImport && ($lastImport->isNew() || $lastImport->isInProcess())) {
//            flash(__('cms-imports::admin.exception.import_in_process'))->error();
//            return redirect()->route('admin.imports.index');
//        }

        $formRequest = app($this->createRequest());
        try {
            $model = DB::transaction(function () use ($formRequest, $user) {
                $model = $this->service->createRow(Import::TYPE_PRODUCT, $user);

                $this->uploadFiles($model, $formRequest);

                return $model;
            });

            $pathToFile = storage_path('app/public/imports/' . $model->file);
            ProductImportJob::dispatch($pathToFile, $model);

            flash(__('cms-core::admin.layout.Data successfully created'))->success();

            return redirect()->route('admin.imports.index');
        } catch (Throwable $e) {
            if (app()->isLocal()) {
                throw $e;
            }
            report($e);
            flash(__('cms-core::admin.layout.Error creating data'))->error();
            return redirect()->back()->withInput($formRequest->input());
        }
    }
}

