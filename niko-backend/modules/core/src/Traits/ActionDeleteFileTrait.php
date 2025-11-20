<?php

namespace WezomCms\Core\Traits;

use WezomCms\Core\Traits\Model\FileAttachable;

trait ActionDeleteFileTrait
{
    /**
     * @param $id
     * @param  string  $field
     * @param  string|null  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteFile($id, $field = 'file', $locale = null)
    {
        /** @var FileAttachable $obj */
        $obj = $this->modelDeleteFile()::findOrFail($id);

        $obj->deleteFile($field, $locale);

        flash(__('cms-core::admin.layout.File successfully deleted'), 'success');

        return redirect()->back();
    }

    /**
     * @return string
     */
    protected function modelDeleteFile(): string
    {
        return $this->model();
    }
}
