<?php

namespace WezomCms\Core\Traits;

use Illuminate\Http\Request;
use WezomCms\Catalog\Models\Model;
use WezomCms\Core\Traits\Model\FileAttachable;

trait ActionDeleteFileTrait
{
    /**
     * @param $id
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteFile($id, Request $request)
    {
        /** @var Model|FileAttachable $obj */
        $obj = $this->modelDeleteFile()::findOrFail($id);

        $obj->deleteFile($request->input('field', 'file'), $request->input('locale'));

        if (app('request')->expectsJson()) {
            return $this->success();
        } else {
            flash(__('cms-core::admin.layout.File successfully deleted'), 'success');

            return redirect()->back();
        }
    }

    /**
     * @return string|Model
     */
    protected function modelDeleteFile(): string
    {
        return $this->model();
    }
}
