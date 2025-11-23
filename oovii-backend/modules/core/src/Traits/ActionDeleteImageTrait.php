<?php

namespace WezomCms\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use WezomCms\Core\Traits\Model\ImageAttachable;

trait ActionDeleteImageTrait
{
    /**
     * @param $id
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deleteImage($id, Request $request)
    {
        /** @var Model|ImageAttachable $obj */
        $obj = $this->modelDeleteImage()::findOrFail($id);

        $obj->deleteImage($request->input('field', 'image'), $request->input('locale'));

        if (app('request')->expectsJson()) {
            return $this->success();
        } else {
            flash(__('cms-core::admin.layout.Image successfully deleted'), 'success');

            return redirect()->back();
        }
    }

    /**
     * @return string|Model
     */
    protected function modelDeleteImage(): string
    {
        return $this->model();
    }
}
