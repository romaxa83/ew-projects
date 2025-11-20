<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Schema;
use WezomCms\Core\Contracts\ImageMultiUploaderControllerInterface;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\ImageMultiUploaderAttachable;

class ImageMultiUploaderController extends AdminController implements ImageMultiUploaderControllerInterface
{
    use AjaxResponseStatusTrait;

    /**
     * @var string
     */
    protected $model;

    /**
     * ImageMultiUploaderController constructor.
     * @param  Request  $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->model = $request->get('model');
    }

    /**
     * @return Model|ImageMultiUploaderAttachable
     */
    public function getModel(): Model
    {
        $modelName = decrypt($this->model);
        $model = new $modelName();

        // Check if model extend ImageMultiUploaderAttachable trait
        if (!method_exists($model, 'getMainColumn')) {
            $this->error(__('cms-core::admin.layout.The model must have a method getMainColumn'));
        }

        return $model;
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            return $this->error();
        }

        $mainId = $request->get('main_id');
        if (!$mainId) {
            return $this->error();
        }

        /** @var ImageMultiUploaderAttachable|Model $obj */
        $obj = $this->getModel();

        $obj->setAttribute($obj->getMainColumn(), $mainId);

        // Upload image.
        $obj->uploadImage($file);

        // Set sort position
        if (Schema::hasColumn($obj->getTable(), 'sort')) {
            $obj->setAttribute('sort', (int) $request->get('sort_position', 1));
        }

        // Set default
        if (Schema::hasColumn($obj->getTable(), 'default')) {
            $default = $obj->where($obj->getMainColumn(), $mainId)
                ->where('default', true)
                ->doesntExist();

            $obj->setAttribute('default', $default);
        }

        $obj->save();

        return $this->success();
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function getUploadedImages(Request $request): JsonResponse
    {
        $options = $request->get('options', []);
        $model = $this->getModel();

        /** @var Collection $images */
        $query = $model->where($model->getMainColumn(), $request->get('main_id'));
        if (Schema::hasColumn($model->getTable(), 'default')) {
            $options['default_image'] = true;
            $query->orderByDesc('default');
        } else {
            $options['default_image'] = false;
        }

        $images = $query->orderBy('sort')
            ->latest('id')
            ->get()
            ->filter(function ($image) {
                /** @var $image ImageMultiUploaderAttachable */
                return $image->imageExists();
            });

        $count = $images->count();
        if ($count) {
            $html = view(
                'cms-core::admin.partials.image-multi-uploader.uploaded-images',
                compact('images', 'options', 'model')
            )->render();
        } else {
            $html = view('cms-core::admin.partials.image-multi-uploader.no-images')
                ->render();
        }

        return $this->success(compact('html', 'count'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function delete(Request $request): JsonResponse
    {
        /** @var ImageMultiUploaderAttachable $row */
        $row = $this->getModel()->findOrFail($request->get('id'));
        if ($row) {
            $row->delete();
        }

        return $this->success([
            'not_uploaded_images_div' => view('cms-core::admin.partials.image-multi-uploader.no-images')
                ->render(),
        ]);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function setAsDefault(Request $request): JsonResponse
    {
        $model = $this->getModel();

        $row = $model->findOrFail($request->get('id'));
        if (!$row) {
            return $this->error();
        }

        $model->where($model->getMainColumn(), $row->{$model->getMainColumn()})
            ->update(['default' => 0]);

        $row->default = 1;
        $row->save();

        return $this->success();
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function sort(Request $request): JsonResponse
    {
        $positions = $request->get('positions');

        foreach ($positions as $position => $id) {
            $row = $this->getModel()->findOrFail($id);
            $row->sort = $position + 1;
            $row->save();
        }

        return $this->success();
    }

    /**
     * @param $id
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function renameForm($id, Request $request)
    {
        $model = $this->getModel();

        /** @var ImageMultiUploaderAttachable $obj */
        $obj = $model::findOrFail($id);

        abort_if(!$obj->renamePopup(), 404);

        return view(
            'cms-core::admin.partials.image-multi-uploader.rename-modal',
            ['obj' => $obj, 'model' => $model, 'locales' => app('locales')]
        );
    }

    /**
     * @param $id
     * @param  Request  $request
     * @return JsonResponse
     */
    public function rename($id, Request $request): JsonResponse
    {
        $model = $this->getModel();

        /** @var ImageMultiUploaderAttachable $obj */
        $obj = $model::findOrFail($id);

        abort_if(!$obj->renamePopup(), 404);

        // validate
        if ($formRequest = $obj->renameFormRequest() !== null) {
            app($formRequest); // Resolve request && automatically validate
        }

        \DB::transaction(function () use ($obj, $request) {
            foreach (array_except($obj->customPopupFields(), 'translatable') as $field) {
                $obj->{$field['name']} = $request->input($field['name']);
            }

            foreach (app('locales') as $locale => $language) {
                $translatedObj = $obj->translateOrNew($locale);
                if ($obj->hasNameField()) {
                    $translatedObj->name = $request->input($locale . '.name');
                }
                if ($obj->hasAltAndTitleFields()) {
                    $translatedObj->alt = $request->input($locale . '.alt');
                    $translatedObj->title = $request->input($locale . '.title');
                }

                foreach (array_get($obj->customPopupFields(), 'translatable', []) as $field) {
                    $translatedObj->{$field['name']} = $request->input("{$locale}.{$field['name']}");
                }
            }
            $obj->save();
        });

        return $this->success(
            ['message' => __('cms-core::admin.layout.Data successfully updated'), 'action' => 'close-modal']
        );
    }
}
